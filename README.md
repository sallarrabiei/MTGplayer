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
- **Caching**: Redis (for improved performance)

## Installation

### Prerequisites

- PHP 8.1+
- Composer
- Node.js 16+
- MySQL/PostgreSQL or SQLite
- Redis (optional, for caching)

### Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/sallarrabiei/MTGplayer.git
   cd MTGplayer
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

4. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

6. **Configure your database** in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mtg_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. **Configure Cardmarket API** in `.env`:
   ```
   CARDMARKET_APP_TOKEN=your_app_token
   CARDMARKET_APP_SECRET=your_app_secret
   CARDMARKET_ACCESS_TOKEN=your_access_token
   CARDMARKET_ACCESS_SECRET=your_access_secret
   ```

8. **Configure Redis** (optional) in `.env`:
   ```
   CACHE_STORE=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

9. **Run database migrations**:
   ```bash
   php artisan migrate
   ```

10. **Import card data**:
    ```bash
    php artisan mtg:import "https://your-json-url.com/cards.json"
    ```

11. **Build frontend assets**:
    ```bash
    npm run build
    ```

12. **Start the development server**:
    ```bash
    php artisan serve
    ```

    In a separate terminal:
    ```bash
    npm run dev
    ```

## Usage

1. **Search**: Enter a card name or keyword into the search bar.
2. **Filters**: Narrow results using dropdowns for set, color, or mana cost.
3. **Pricing**: View current Cardmarket prices for matched cards.
4. **Interface**: Navigate through a clean, responsive UI styled for MTG.

## API Endpoints

### Public Endpoints

- `GET /api/cards` - Search and filter cards
  - Query parameters: `name`, `set`, `rarity`, `colors`, `mana_cost`, `cmc`, `type`, `sort`, `order`
- `GET /api/cards/filters` - Get available filter options
- `GET /api/cards/{id}` - Get details for a specific card
- `POST /api/cards/update-prices` - Update card prices from Cardmarket

### Authenticated Endpoints (requires Laravel Sanctum)

- `GET /api/favorites` - Get user's favorite cards
- `POST /api/favorites` - Add a card to favorites
- `DELETE /api/favorites/{cardId}` - Remove a card from favorites
- `GET /api/favorites/check/{cardId}` - Check if a card is favorited

## Development

### Backend Structure

- **Models**: `app/Models/Card.php`, `app/Models/Favorite.php`
- **Controllers**: `app/Http/Controllers/Api/CardController.php`, `app/Http/Controllers/Api/FavoriteController.php`
- **Services**: `app/Services/CardmarketService.php` - Handles Cardmarket API integration
- **Commands**: `app/Console/Commands/ImportMTGCards.php` - Import cards from JSON

### Frontend Structure

- **Components**: `resources/js/components/` - Reusable Vue components
- **Pages**: `resources/js/pages/` - Main page components
- **Stores**: `resources/js/stores/` - Pinia state management
- **Router**: `resources/js/app.js` - Vue Router configuration

### Key Implementation Details

1. **Data Import**: The `mtg:import` command fetches JSON data and intelligently maps fields to the database schema, handling various JSON formats.

2. **Caching Strategy**: 
   - Card searches cached for 5 minutes
   - Individual card details cached for 1 hour
   - Filter options cached for 1 hour
   - Cardmarket prices cached for 1 hour

3. **Price Updates**: Prices are fetched on-demand when viewing cards and cached. Bulk price updates are rate-limited to respect Cardmarket API limits.

4. **Search Performance**: Database indexes on `name`, `set_code`, `rarity`, and `cardmarket_id` ensure fast queries.

> **Tip**: For better performance, cache expensive operations such as database lookups and API calls using Redis.

## Contributing

1. Fork the repository: [https://github.com/sallarrabiei/MTGplayer](https://github.com/sallarrabiei/MTGplayer)
2. Create a new branch: `git checkout -b feature/your-feature`
3. Make your changes and commit: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).
