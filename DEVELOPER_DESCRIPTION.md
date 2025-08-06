## Developer Description for Magic: The Gathering Search Engine

### Project Overview

Build a Magic: The Gathering Search Engine for the repository at **sallarrabiei/MTGplayer**. This web app enables users to search and filter Magic: The Gathering (MTG) cards, importing data from a JSON URL into a database and fetching prices from the **Cardmarket API** for exact matches. Use **Laravel** for the backend, **Vue.js** for the frontend, and **Tailwind CSS** for styling to create a fast, intuitive, and responsive tool. :rocket:

> **Important**
> • Import card data from a JSON URL into a database.  
> • Use the Cardmarket API for pricing exact matches.

---

### Requirements

#### Backend (Laravel)

1. **Framework**: Use Laravel to expose a RESTful API.
2. **Data Import**: Fetch and import MTG card data (name, set, rarity, `cardmarket_id`, etc.) from a provided JSON URL into a **MySQL** or **PostgreSQL** database.
3. **Cardmarket API**: Query the Cardmarket API to fetch prices for 100 % matches (e.g., by `cardmarket_id`, `name`, `set`).
   - Suggested endpoint: `GET /cards/cardmarket/:id` for specific cards.
   - Handle authentication (restricted to Widget / 3rd-party apps or professional accounts).
4. **Endpoints**
   - `GET /api/cards` – Query cards from the database (e.g., `?name=Black+Lotus&set=Alpha`).
   - *(Optional)* `POST /api/favorites` – Save favorites for authenticated users.
5. **Caching**: Use Redis or Laravel’s caching layer for expensive DB queries and external API responses.
6. **Error Handling**: Gracefully manage JSON-import failures, API rate limits (e.g., 30 000 requests / day), or non-matching cards.

> **Warning**  
> Ensure compliance with Cardmarket API restrictions and rate limits (30 000 requests / day; 503 *Slow Down* for per-minute limits).

#### Frontend (Vue.js)

1. **Framework**: Single-page application in Vue.js.
2. **Components**
   - **Search Bar** – Debounced input for card-name searches.
   - **Filters** – Dropdowns / checkboxes for set, color, mana cost, etc.
   - **Card Display** – Grid / list view for card details (image, name, price, etc.).
   - **Loading States** – Spinners or skeletons during data fetches.
3. **Routing**: Vue Router for navigation.
4. **State Management**: Pinia or Vuex if global state becomes complex.
5. **Performance**: Optimize rendering; lazy-load images.

#### Styling (Tailwind CSS)

1. **Framework**: Tailwind CSS utility-first styling.
2. **Design Goals**
   - Clean, modern UI.
   - Fully responsive (mobile, tablet, desktop).
   - Card-like layout with hover effects.
3. **Customization**: Extend `tailwind.config.js` with MTG-themed colors (blue, red, green, etc.).
4. **Assets**: Use card images from JSON data when available, or placeholders at `public/assets/images/card.png`.

> **Tip** Use relative links for images to ensure compatibility when the repository is cloned.

---

### Additional Requirements

- **Performance**: Optimize database queries, JSON imports, and Cardmarket API calls.
- **Responsiveness**: Ensure cross-device compatibility.
- **Testing**: Write tests for Laravel API and Vue components.
- **Deployment**: Prepare configurations for Vercel, Heroku, or AWS.
- **Documentation**: Update `README.md` with key information.

---

### Deliverables

1. Complete source code in the **sallarrabiei/MTGplayer** repository.
2. Automated test suite for backend and frontend.
3. Responsive UI styled with Tailwind CSS.
4. *(Optional)* Authentication & favorites feature.

---

### Notes

- Import card data from the provided JSON URL efficiently; handle large datasets.
- Use the Cardmarket API for pricing, matching cards by `cardmarket_id`, name, set, etc., to achieve 100 % accuracy.
- Vue.js is preferred, but you may propose alternatives (e.g., React) with clear rationale if better suited.
- Keep code modular and well-documented.
- Clarify JSON URL, its structure, or Cardmarket API credentials if needed.

> **Note** Follow GitHub Markdown guidelines for all documentation.