@extends('layouts.app')
@section('title','My activity')
@section('content')
<section class="activity-page">
    <div class="activity-heading">
        <div><div class="eyebrow">YOUR CORNER OF CAMPUS</div><h1>My activity</h1><p>Keep track of the things you’ve shared and claimed.</p></div>
        <a class="button button-primary" href="{{ route('items.create') }}">＋ Report item</a>
    </div>

    @if(app()->environment('local'))
        <div class="glass aside-card" style="margin-bottom:1.5rem">
            <div class="eyebrow">TWO PERSON DEMO</div><h3>Try both sides of a claim</h3>
            <p>Maya is the claimant. Alex found the sample bottle and can approve or decline her private claim.</p>
            <div class="demo-switches">
                <a class="button button-secondary" href="{{ route('demo.login') }}">Switch to Maya · Claimant</a>
                <a class="button button-secondary" href="{{ route('demo.login',['poster'=>1]) }}">Switch to Alex · Finder</a>
            </div>
        </div>
    @endif

    @if($pendingReceived > 0 && $nextPendingItem)
        <div class="glass aside-card" style="margin-bottom:1.5rem;border-color:rgba(129,211,190,.35)">
            <div class="eyebrow">CLAIM NEEDS YOUR REVIEW</div>
            <h3>You have {{ $pendingReceived }} pending {{ Str::plural('claim',$pendingReceived) }}</h3>
            <p>Open your found listing to read the private ownership detail and approve or decline the claim.</p>
            <a class="button button-primary" href="{{ route('items.show',$nextPendingItem) }}">Review claim for {{ $nextPendingItem->name }} ↗</a>
        </div>
    @endif

    <div class="activity-columns">
        <div class="activity-column">
            <div class="section-head compact"><div><h2>My listings <span>{{ $items->total() }}</span></h2><p>Manage your posts and review incoming claims.</p></div></div>
            @forelse($items as $item)
                <article class="activity-card glass"><div class="activity-card-top"><span class="item-kind {{ $item->type }}">{{ strtoupper($item->type) }}</span><span class="status-pill status-{{ $item->status }}">{{ ucfirst($item->status) }}</span></div><h3><a href="{{ route('items.show',$item) }}">{{ $item->name }}</a></h3><p>⌖ {{ $item->location }} · {{ $item->date_seen->format('M j') }}</p><div class="activity-card-foot"><span>{{ $item->pending_claims_count ? $item->pending_claims_count.' pending '.Str::plural('claim',$item->pending_claims_count) : 'No pending claims' }}</span><a href="{{ route('items.show',$item) }}">{{ $item->pending_claims_count ? 'Review claim ↗' : 'Manage ↗' }}</a></div></article>
            @empty
                <div class="empty-state glass"><h3>No posts yet</h3><p>Start with a lost or found report.</p><a href="{{ route('items.create') }}" class="button button-primary">Create a listing</a></div>
            @endforelse
            <div class="pagination">{{ $items->links() }}</div>
        </div>
        <div class="activity-column">
            <div class="section-head compact"><div><h2>My claims <span>{{ $claims->count() }}</span></h2><p>Updates on items you’re trying to reclaim.</p></div></div>
            @forelse($claims as $claim)
                <article class="activity-card glass"><div class="activity-card-top"><span class="eyebrow muted">CLAIM · {{ $claim->created_at->format('M j') }}</span><span class="status-pill status-{{ $claim->status }}">{{ ucfirst($claim->status) }}</span></div><h3><a href="{{ route('items.show',$claim->item) }}">{{ $claim->item->name }}</a></h3><p>{{ $claim->proof }}</p>@if($claim->response_note)<div class="response-note">Finder’s note: {{ $claim->response_note }}</div>@endif @if($claim->status==='approved')<a href="mailto:{{ $claim->item->user->email }}" class="claim-contact">Contact finder {{ $claim->item->user->email }} ↗</a>@endif</article>
            @empty
                <div class="empty-state glass"><h3>No claims sent</h3><p>When something looks familiar, send the finder a private ownership detail.</p><a href="{{ route('home') }}" class="button button-secondary">Explore items</a></div>
            @endforelse
        </div>
    </div>
</section>
@endsection
