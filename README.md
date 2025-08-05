# Magic: The Gathering Search Engine

The Magic: The Gathering Search Engine is a web application for searching and filtering Magic: The Gathering (MTG) cards with pricing data from the Cardmarket API. Built with Laravel, Vue.js, and Tailwind CSS, the application imports card data from a JSON URL into a database to provide a fast and intuitive user experience.

## Key Features

- **Card Search**: Search for cards by name, set, rarity, or other attributes.
- **Advanced Filters**: Refine results by color, mana cost, keywords, and more.
- **Pricing**: Fetches real-time prices from the Cardmarket API for exact card matches.
- **Responsive Design**: Optimized for use on desktop, tablet, and mobile devices.
- **Dynamic UI**: Vue.js enables a smooth, single-page application experience.
- **Favorites (Optional)**: Logged-in users can save cards to their favorites.

> **Note**: Card data is imported from a remote JSON file and stored in a database. Prices are retrieved via the Cardmarket API for exact matches.

## Tech Stack

- **Backend**: Laravel (API, database operations, Cardmarket integration)
- **Frontend**: Vue.js (reactive UI, components)
- **Styling**: Tailwind CSS (responsive, utility-first design)
- **Database**: MySQL or PostgreSQL (for storing card data)
- **Data Source**: JSON file fetched from a URL
- **External API**: Cardmarket API (for card pricing)

## Usage

1. **Search**: Enter a card name or keyword into the search bar.
2. **Filters**: Narrow results using dropdowns for set, color, or mana cost.
3. **Pricing**: View current Cardmarket prices for matched cards.
4. **Interface**: Navigate through a clean, responsive UI styled for MTG.

## Development

- **Backend**:
  - Laravel manages API routes such as `/api/cards`.
  - Imports and parses card data from a JSON URL.
  - Queries the Cardmarket API for pricing information.
  - Use Redis to cache database queries and API responses for improved performance.

- **Frontend**:
  - Vue components are located in `resources/js/components/`.
  - Vue Router handles navigation within the single-page app.

- **Styling**:
  - Tailwind CSS is configured in `resources/css/app.css`.
  - Customize with MTG-themed colors (e.g., blue, red, green) as needed.

> **Tip**: For better performance, cache expensive operations such as database lookups and API calls using Redis.

## Contributing

1. Fork the repository: [https://github.com/sallarrabiei/MTGplayer](https://github.com/sallarrabiei/MTGplayer)
2. Create a new branch:
