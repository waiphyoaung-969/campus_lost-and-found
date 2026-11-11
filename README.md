# Campus Loop

A responsive Laravel campus lost and found app based on the supplied project proposal. It includes Microsoft campus sign-in, lost and found reports, private photos, searchable listings, user-owned post management, direct ownership claims, and a separate admin activity overview.

## Run locally

Requirements: PHP 8.3+, Composer, and SQLite. Enable PHP `fileinfo` for image validation and uploads.

1. Install PHP dependencies: `composer install`
2. Copy `.env.example` to `.env` and set `APP_KEY` with `php artisan key:generate`.
3. Prepare the database and sample records: `php artisan migrate --seed`
4. Start the site: `php artisan serve`
5. Open `http://localhost:8000`.

In local mode, **Demo** opens one chooser where you can sign in as Maya Chen (claimant) or Alex Morgan (finder). Use `http://localhost:8000/demo-login?admin=1` to open the admin overview.

### Walk through a claim

The seeded demo includes a pending private claim from Maya Chen on Alex Morgan’s **Blue insulated bottle** listing.

1. Click **Demo** and choose Maya Chen to see her pending claim in **My activity**.
2. From My activity, choose **Switch to Alex · Finder**, or click **Demo** again and choose Alex Morgan.
3. Alex’s My activity page highlights the claim waiting for review. Open the listing, read Maya’s private ownership detail, then choose **Approve** or **Decline**.
4. Switch back to Maya. Her My claims card shows the result. If Alex approves, Maya can contact the finder from that card or the listing page.

The demo account switch links are only shown in local development mode. The sample claim remains pending until the finder chooses a decision.

## Microsoft sign-in

Register an app in the Microsoft identity platform, add a Web redirect URI that matches `MICROSOFT_REDIRECT_URI`, then set `MICROSOFT_CLIENT_ID`, `MICROSOFT_CLIENT_SECRET`, and `MICROSOFT_TENANT_ID` in `.env`. Set `CAMPUS_EMAIL_DOMAIN` to restrict sign-in to your institution and `CAMPUS_ADMIN_EMAIL` to grant the admin overview to one account. Microsoft Graph `User.Read` is used to read the signed-in member's name and campus email.

## Workflows

- Members can report lost or found items, attach up to three private photos, filter by keyword, type, category, date and location, edit their own posts, and close or resolve them.
- Found item pages withhold descriptions and photos. Claimants describe a private identifying detail; the finder approves or rejects each claim. Only an approved claimant can view the finder's email and private photos.
- Listings older than 60 days leave the active search results. They remain available to the original poster in My Activity.
- The admin view shows aggregate activity and recent listing status. Claim decisions remain between each finder and claimant, matching the peer to peer flow in the proposal.

The interface is built with Blade, plain CSS and JavaScript. Photos are stored on Laravel's private local disk; the browser compresses them before upload, with server-side image and size validation.
