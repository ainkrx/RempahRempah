# 🌶️ Rempah Rempah
A full-stack web application for discovering, publishing, and cooking Indonesian local recipes, with a verification workflow that routes every public recipe through an administrator and a nutritionist before it goes live. Built with **Laravel** (PHP), Blade, and a relational database.  
🔗 **Live Demo:** https://rempahrempah.vercel.app  
Developed on **Vercel** and the database runs on **Neon** (PostgreSQL).
## Background
This project was developed by three final-year Computer Science students as an undergraduate thesis project. The application follows Laravel's MVC pattern and focuses on practical full-stack concepts: relational data modelling, server-rendered pages, session authentication, multi-role authorization, file uploads, transactional email, and CRUD operations.

The product idea is preservation of Indonesian culinary heritage: members publish local recipes, an administrator screens them, and an *ahli gizi* (nutritionist) fills in the nutrition facts before the recipe becomes publicly visible. The interface is written in Indonesian.

The project was originally built with MySQL, but for deployment purposes it was containerized with Docker as well, so it's also easier for other people to run.
## Deployment Notes
Uploaded images and videos are saved inside the current container, which will be displayed while that container is running, but disappear after the service sleeps, restarts, or redeploys. Neon stores the database records and file paths only and does not store the uploaded files. That's why the live-demo database is intentionally reset to its seeded state whenever the service starts.

Registration, password reset, and every recipe-verification status change send real email through SMTP, so `MAIL_*` must point at a working mailbox for those flows to complete. On the live demo those emails are the only way to receive a password-reset code or a verification notice, so any flow that waits on one stops there if the mailbox is not configured. Those emails are sent inside the request rather than through a queue, so the pages that trigger one take a moment longer to respond.
## License
Learning purposes. All images belong to their respective owners.

# Project Overview
## Backend (Laravel / PHP)
- MVC structure with Eloquent models: `User`, `Recipe`, `Category`, `Tag`, `Tool`, `Ingredient`, `IngredientHeader`, `Step`, `StepHeader`, `Nutrition`, `Review`, `ReviewReaction`, `Comment`, `Reply`, `Bookmark`, `Company`, `AvoidedIngredient`, `CookingHistory`, `StepProgress`, `UserIngredientProgress`, `UserToolProgress`, and `follows`
- Relational data modelling for recipes, grouped ingredients and steps, tools, tags, nutrition facts, reviews, comments, bookmarks, follows, and per-user cooking progress
- Role-based access for `guest`, `member`, `admin`, and `ahli_gizi`, enforced by custom route middleware (`member`, `admin`, `ahligizi`, `guest`, `loggedin`, `adminormember`, `adminorahligizi`)
- Two-stage recipe verification: a public recipe is auto-assigned to the administrator with the smallest pending queue, who approves or rejects it with a written reason; on approval it is auto-assigned to the nutritionist with the smallest pending queue, who fills in the nutrition facts and total/fat energy to publish it
- Exclusive recipes posted by an administrator skip the administrator stage and go straight to a nutritionist, and can carry a sponsoring company
- Recipe discovery through name and ingredient search, multi-select category filtering across three category groups (Kategori Utama, Hari Spesial, Daerah), duration filtering, tag filtering, and sorting by date, average rating, or review count
- Per-user avoided-ingredient preferences that filter matching recipes out of both search and typeahead results
- Session-based registration, login, logout, and remember-me authentication, plus password reset via a time-limited emailed token stored in `password_resets`
- Transactional email for welcome, password reset, and each verification status change, rendered from a single reusable Blade mail template
- Cooking progress tracked per tool, ingredient, and step, persisted to the database for signed-in members and to the session for guests, with automatic cooking-history entries once every step of a recipe is done
- Reviews with 1–5 star ratings, optional photos, like/dislike reactions, and a self-review guard; threaded comments with replies, deletable by their author
- Cached lookups (`category_all`, `tag_all`, `top_ingredients`) shared into every view from the page controller
- Database migrations and seeders for users, categories, companies, 40 recipes, ingredients, tools, tags, nutrition, reviews, reactions, bookmarks, follows, comments, replies, cooking history, and every progress and pivot table
## Frontend (Blade)
- Shared layout with a category/duration mega-navigation, recipe typeahead search, and a role-aware profile dropdown
- Home page with a sponsored-company banner carousel, a top-rated `Rekomendasi` section, and a `Masak Kilat` section for recipes under 30 minutes
- Search page with combined name, category, duration, and tag filters, sorting, and pagination
- Recipe detail page with media, tools, grouped ingredients, grouped steps, nutrition facts, tickable cooking progress, reviews, and a comment thread
- Add and edit recipe form with dynamic ingredient and step groups, tag typeahead, and image/video upload
- Onboarding `welcome` flow that asks a new member for avoided ingredients before unlocking the rest of the app
- Member pages for profile, password, preferences, own recipes, bookmarks, reviews, cooking progress, and cooking history
- Public profile pages with follow/unfollow, follower and following counts
- `Tentang kami` page describing the product idea and the team behind it
- Recipe verification queue for administrators and nutritionists, with approve, reject-with-reason, and add-nutrition modals
- Administrator directories of members and nutritionists with activity and verification statistics
## Structure
```text
RempahRempah/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Page, user, recipe, review, comment, bookmark,
│   │   │                    # nutrition, progress, and email controllers
│   │   └── Middleware/      # Role and authentication middleware
│   ├── Mail/                # Reusable general-purpose mailable
│   └── Models/              # Eloquent models
├── database/
│   ├── migrations/          # Database schema
│   ├── factories/           # User factory
│   └── seeders/             # Demo users, catalogue data, and pivot data
├── public/
│   ├── assets/              # Logos, banners, and icons
│   ├── css/                 # Page and template stylesheets
│   ├── js/                  # Recipe form, recipe detail, and cooking progress scripts
│   └── index.php            # Web entry point
├── resources/
│   └── views/               # Blade templates, shared templates, and modals
├── routes/
│   ├── web.php              # Browser routes
│   └── api.php              # Sanctum-protected current-user endpoint
├── .env.example             # Environment-variable template
├── composer.json            # PHP dependencies
└── package.json             # Frontend build dependencies
```

# 🚀 Quick Start Guide
## Step 1: Set Up Environment Variables
Copy `.env.example` to `.env`, then fill in the values. The database values already match the `db` service in `compose.yaml`, so the blanks that still need filling are `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS`. `MAIL_PASSWORD` is a Gmail App Password from an account with 2-Step Verification on, not the account's own password, and leaving these blank makes registration, password reset, and recipe verification fail. Leave `DATABASE_URL` empty unless you are pointing the app at a hosted database such as Neon.
## Step 2: Generate an APP_KEY
```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```
Paste the output into `APP_KEY` in `.env`.
## Step 3: Build and Run
```bash
docker compose up --build
```
## Step 4: Set Up the Database
```bash
docker compose exec server php artisan migrate --seed
```

# 🎮 Test the App
Four things here depend on a working mailbox: registering a new account, resetting a password, and the two verification outcomes in steps 7 and 8, which email the recipe's author. When `MAIL_*` is not configured those steps end where the email would have arrived — registration in particular fails outright rather than skipping the email. Signing in with the seeded demo accounts below avoids all four.
## Guest Preview
Visit `/` to browse the home feed, or `/search` to filter the catalogue. Guests can open any published recipe and tick off tools, ingredients, and steps, but that progress is kept in the session only. Signing in is required to review, comment, bookmark, follow, publish a recipe, or keep progress across devices.
## Demo Accounts
The demo accounts are seeded automatically with `docker compose exec server php artisan migrate --seed`:
1. **Administrator**
  - **Email**: `admin1@gmail.com`
  - **Password**: `wadimor`
2. **Nutritionist (Ahli Gizi)**
  - **Email**: `ahligizi1@gmail.com`
  - **Password**: `wadimor`
3. **Member**
  - **Email**: `CahayaGemilang@gmail.com`
  - **Password**: `CahayaGemilang`
  - Seeded members start with account status `new`, so the first sign-in lands on the `/welcome` onboarding page.
## Try These Actions
1. **Explore the home feed**: Browse the sponsored-company carousel, the `Rekomendasi` top-rated row, and the `Masak Kilat` row of recipes under 30 minutes.
2. **Search and filter**: Open `/search`, then combine a name or ingredient query with category, duration, and tag filters, and sort by date, rating, or review count.
3. **Onboard a new member**: Sign in as a seeded member and pick avoided ingredients on `/welcome`, or skip. Those ingredients are then filtered out of every search result.
4. **Cook a recipe**: Open a recipe, then tick its tools, ingredients, and steps. Completing every step records an entry in `/myCookingHistory`; `/myCookingProgress` lists everything still in progress, and progress can be reset per recipe.
5. **Review and discuss**: Rate a recipe 1–5 with an optional photo, like or dislike other reviews, and post a comment or reply. You cannot review your own recipe.
6. **Publish a recipe**: Use `/addRecipe` to submit one, choosing `private` to keep it to yourself or `public` to send it into verification. Track its status from `/myRecipes` and edit it from `/editRecipe/{id}` after a rejection.
7. **Verify as administrator**: Sign in as the administrator, open `/recipeVerification`, and approve a pending recipe with the confirmation word `terima`, or reject it with a written reason. Both outcomes email the recipe's author.
8. **Add nutrition as nutritionist**: Sign in as a nutritionist, open `/recipeVerification`, and fill in nutrition values plus total and fat energy. Saving publishes the recipe and emails the author.
9. **Administrator directories**: Visit `/viewMembers` and `/viewAhliGizi` for member activity and per-nutritionist verification counts.
10. **Follow and manage a profile**: Open any `/publicProfile/{id}` to follow or unfollow, then update details from `/myProfile`, the password from `/myPassword`, avoided ingredients from `/myPreferences`, and the profile picture with the built-in cropper.
11. **Reset a password**: Sign out, open `/resetPassword`, and request a token. A code is emailed and stays valid briefly before expiring.
12. **Read the background**: Open `/aboutUs` for the product idea behind the catalogue and the team that built it.
