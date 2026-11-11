<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->input('status', 'active'), ['active','resolved','closed','all'], true) ? $request->input('status', 'active') : 'active';
        $items = Item::with('user')
            ->when($status === 'active', fn ($q) => $q->where('status','open')->where('created_at','>=',now()->subDays(60)))
            ->when($status === 'resolved', fn ($q) => $q->where('status','resolved'))
            ->when($status === 'closed', fn ($q) => $q->where('status','closed'))
            ->when($status === 'all', fn ($q) => $q->where(fn ($s) => $s->where(fn ($active) => $active->where('status','open')->where('created_at','>=',now()->subDays(60)))->orWhereIn('status',['resolved','closed'])))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($s) => $s->where('name', 'like', '%'.$request->q.'%')->orWhere('location', 'like', '%'.$request->q.'%')))
            ->when($request->filled('type') && $request->type !== 'all', fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category') && $request->category !== 'all', fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('location'), fn ($q) => $q->where('location','like','%'.$request->location.'%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date_seen', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date_seen', '<=', $request->to))
            ->latest()->paginate(12)->withQueryString();
        return view('home', compact('items'));
    }

    public function microsoftStart(Request $request)
    {
        abort_unless(config('services.microsoft.client_id') && config('services.microsoft.client_secret'), 503, 'Add Microsoft OAuth credentials to .env to enable campus sign-in.');
        $state = Str::random(40); $request->session()->put('oauth_state', $state);
        $query = http_build_query(['client_id'=>config('services.microsoft.client_id'),'response_type'=>'code','redirect_uri'=>config('services.microsoft.redirect'),'response_mode'=>'query','scope'=>'openid profile email User.Read','state'=>$state]);
        return redirect('https://login.microsoftonline.com/'.config('services.microsoft.tenant').'/oauth2/v2.0/authorize?'.$query);
    }

    public function microsoftCallback(Request $request)
    {
        abort_unless($request->state && hash_equals((string)$request->session()->pull('oauth_state'), (string)$request->state), 419, 'Sign-in session expired. Please try again.');
        $token = Http::asForm()->post('https://login.microsoftonline.com/'.config('services.microsoft.tenant').'/oauth2/v2.0/token', ['client_id'=>config('services.microsoft.client_id'),'client_secret'=>config('services.microsoft.client_secret'),'grant_type'=>'authorization_code','code'=>$request->code,'redirect_uri'=>config('services.microsoft.redirect'),'scope'=>'openid profile email User.Read'])->throw()->json('access_token');
        $profile = Http::withToken($token)->get('https://graph.microsoft.com/v1.0/me', ['$select'=>'displayName,mail,userPrincipalName'])->throw()->json();
        $email = strtolower($profile['mail'] ?: $profile['userPrincipalName']);
        $domain = config('services.microsoft.campus_domain');
        abort_if($domain && !str_ends_with($email, '@'.strtolower($domain)), 403, 'Use your campus Microsoft account.');
        $user = User::firstOrCreate(['email'=>$email], ['name'=>$profile['displayName'] ?? $email, 'password'=>Str::random(48)]);
        if (config('services.microsoft.admin_email') && $email === strtolower(config('services.microsoft.admin_email')) && !$user->is_admin) $user->forceFill(['is_admin'=>true])->save();
        auth()->login($user, true); $request->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    public function demoLogin(Request $request)
    {
        abort_unless(app()->environment('local'), 404);
        if (!$request->boolean('admin') && !$request->boolean('poster') && !$request->boolean('claimant')) {
            return view('demo-users');
        }
        $admin = (bool) $request->query('admin');
        $poster = (bool) $request->query('poster');
        $email = $admin ? 'admin@campus.demo' : ($poster ? 'student@campus.demo' : 'member@campus.demo');
        $name = $admin ? 'Campus Admin' : ($poster ? 'Alex Morgan' : 'Maya Chen');
        $user = User::firstOrCreate(['email'=>$email], ['name'=>$name,'password'=>Str::random(32),'is_admin'=>$admin]);
        auth()->login($user); $request->session()->regenerate(); return redirect()->route($user->is_admin ? 'admin' : 'home');
    }

    public function logout(Request $request) { auth()->logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('home'); }

    public function create() { return view('items.form', ['item'=>new Item]); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id; $data['status'] = 'open';
        $data['photos'] = $this->storePhotos($request);
        $item = Item::create($data);
        return redirect()->route('items.show', $item)->with('success', 'Your listing is live.');
    }

    public function show(Item $item)
    {
        $item->load('user');
        $isOwner = auth()->id() === $item->user_id;
        if ($isOwner) $item->load('claims.claimant');
        $approved = auth()->check() && $item->claims()->where('claimant_id', auth()->id())->where('status','approved')->exists();
        return view('items.show', compact('item','isOwner','approved'));
    }

    public function photo(Item $item, string $filename)
    {
        abort_unless(auth()->check(), 403);
        $allowed = auth()->id() === $item->user_id || $item->claims()->where('claimant_id', auth()->id())->where('status','approved')->exists();
        abort_unless($allowed, 403);
        abort_unless(in_array($filename, array_map('basename', $item->photos ?? []), true), 404);
        $path = collect($item->photos)->first(fn ($stored) => basename($stored) === $filename);
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return response()->file(Storage::disk('local')->path($path));
    }

    public function edit(Item $item) { $this->ensureOwner($item); return view('items.form', compact('item')); }

    public function update(Request $request, Item $item)
    {
        $this->ensureOwner($item); $data = $this->validated($request); $newPhotos = $this->storePhotos($request);
        if ($newPhotos) { foreach ($item->photos ?? [] as $path) Storage::disk('local')->delete($path); $data['photos'] = $newPhotos; }
        $item->update($data); return redirect()->route('items.show', $item)->with('success','Listing updated.');
    }

    public function close(Item $item, Request $request)
    {
        $this->ensureOwner($item); $item->update(['status'=>$request->input('status') === 'resolved' ? 'resolved' : 'closed']);
        return redirect()->route('my-items')->with('success','Listing status updated.');
    }

    public function mine(Request $request)
    {
        $items = $request->user()->items()->withCount(['claims as pending_claims_count'=>fn($q)=>$q->where('status','pending')])->latest()->paginate(10);
        $claims = $request->user()->claims()->with('item.user')->latest()->get();
        $pendingReceived = $request->user()->items()->whereHas('claims', fn($q)=>$q->where('status','pending'))->count();
        $nextPendingItem = $request->user()->items()->whereHas('claims', fn($q)=>$q->where('status','pending'))->latest()->first();
        return view('items.mine', compact('items','claims','pendingReceived','nextPendingItem'));
    }

    public function claim(Request $request, Item $item)
    {
        abort_unless($item->type === 'found' && $item->status === 'open', 422);
        abort_if($item->user_id === $request->user()->id, 403);
        $data = $request->validate([
            'claimant_name'=>'required|string|min:2|max:120',
            'student_id'=>'required|string|min:3|max:40|regex:/^[A-Za-z0-9-]+$/',
            'proof'=>'required|string|min:12|max:1200',
        ]);
        Claim::updateOrCreate(['item_id'=>$item->id,'claimant_id'=>$request->user()->id], [
            'claimant_name'=>$data['claimant_name'],
            'student_id'=>$data['student_id'],
            'proof'=>$data['proof'],
            'status'=>'pending',
            'response_note'=>null,
        ]);
        return back()->with('success','Claim sent privately to the finder. They can review it in My activity by opening their listing.');
    }

    public function decide(Request $request, Claim $claim)
    {
        abort_unless($claim->item->user_id === $request->user()->id && $claim->status === 'pending', 403);
        $data = $request->validate(['decision'=>'required|in:approved,rejected','response_note'=>'nullable|string|max:500']);
        abort_if($data['decision'] === 'approved' && $claim->item->status !== 'open', 422, 'This item has already been resolved.');
        $claim->update(['status'=>$data['decision'],'response_note'=>$data['response_note'] ?? null]);
        if ($data['decision'] === 'approved') {
            $claim->item->update(['status'=>'resolved']);
            $claim->item->claims()->where('id','!=',$claim->id)->where('status','pending')->update(['status'=>'rejected','response_note'=>'This item has already been returned to its owner.']);
        }
        return back()->with('success','Claim decision saved. '.($data['decision'] === 'approved' ? 'The claimant can now view your contact email.' : ''));
    }

    public function admin()
    {
        abort_unless(auth()->user()->is_admin, 403);
        $stats = ['open'=>Item::where('status','open')->where('created_at','>=',now()->subDays(60))->count(),'resolved'=>Item::where('status','resolved')->count(),'users'=>User::count(),'claims'=>Claim::count(),'pending'=>Claim::where('status','pending')->count()];
        $recent = Item::with('user')->latest()->take(8)->get();
        return view('admin', compact('stats','recent'));
    }

    private function validated(Request $request): array
    {
        $rules = ['type'=>'required|in:lost,found','name'=>'required|string|max:100','category'=>'required|in:Electronics,ID & cards,Bags & accessories,Clothing,Books & stationery,Keys,Other','description'=>'required|string|max:2000','date_seen'=>'required|date|before_or_equal:today','location'=>'required|string|max:160','holding_location'=>'nullable|string|max:160','photos'=>'nullable|array|max:3','photos.*'=>'image|mimes:jpg,jpeg,png,webp|max:4096'];
        $data = $request->validate($rules);
        if ($data['type'] === 'found') $request->validate(['holding_location'=>'required|string|max:160']);
        else $data['holding_location'] = null;
        unset($data['photos']); return $data;
    }

    private function storePhotos(Request $request): array
    {
        $paths = [];
        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store('items/'.auth()->id(), 'local'); $paths[] = $path;
            if (function_exists('imagecreatefromstring')) {
                $raw = @file_get_contents($photo->getRealPath()); $image = $raw ? @imagecreatefromstring($raw) : false;
                if ($image) { ob_start(); imagejpeg($image, null, 78); $jpeg = ob_get_clean(); imagedestroy($image); if ($jpeg) { Storage::disk('local')->put(preg_replace('/\.[^.]+$/','.jpg',$path), $jpeg); Storage::disk('local')->delete($path); $paths[array_key_last($paths)] = preg_replace('/\.[^.]+$/','.jpg',$path); } }
            }
        }
        return $paths;
    }

    private function ensureOwner(Item $item): void { abort_unless(auth()->id() === $item->user_id, 403); }
}
