@extends('layouts.app')
@section('title','Choose a demo user')
@section('content')
<section class="auth-page" style="max-width:850px;margin:5rem auto;padding:0 1.25rem">
    <div class="glass aside-card" style="padding:clamp(1.5rem,5vw,3rem)">
        <div class="eyebrow">CAMPUS LOOP DEMO</div>
        <h1 style="margin:.5rem 0">Choose a demo user</h1>
        <p>Use either side of the lost and found process. The sample claim is already waiting for Alex to review.</p>
        <div class="activity-columns" style="margin-top:2rem">
            <article class="activity-card glass">
                <div class="eyebrow">CLAIMANT</div><h2>Maya Chen</h2>
                <p>View the sample claim and check the finder’s decision.</p>
                <a class="button button-primary full" href="{{ route('demo.login',['claimant'=>1]) }}">Continue as Maya ↗</a>
            </article>
            <article class="activity-card glass">
                <div class="eyebrow">FINDER</div><h2>Alex Morgan</h2>
                <p>Review Maya’s private claim and approve or decline it.</p>
                <a class="button button-primary full" href="{{ route('demo.login',['poster'=>1]) }}">Continue as Alex ↗</a>
            </article>
        </div>
        <p class="helper" style="margin-top:1.5rem">These shared demo accounts are for trying the sample workflow. Avoid adding real personal information.</p>
    </div>
</section>
@endsection
