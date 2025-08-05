# Magic: The Gathering Search Engine 🃏

A comprehensive web application for searching and filtering Magic: The Gathering (MTG) cards with real-time pricing data from the Cardmarket API. Built with Laravel, Vue.js, and Tailwind CSS for a fast, intuitive, and responsive user experience.

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-blue.svg)](https://tailwindcss.com)

## ✨ Key Features

- **🔍 Advanced Search**: Search cards by name, set, rarity, colors, mana cost, and type
- **📊 Real-time Pricing**: Fetches current prices from the Cardmarket API for exact matches
- **🎨 Beautiful UI**: Modern, responsive design optimized for all devices
- **⚡ Fast Performance**: Redis caching for database queries and API responses
- **❤️ Favorites System**: Save and manage your favorite cards (with authentication)
- **📈 Statistics Dashboard**: Comprehensive insights into the card database
- **🎯 Smart Filtering**: Advanced filters with debounced search and pagination

## 🛠 Tech Stack

### Backend
- **Laravel 10.x** - RESTful API and backend logic
- **MySQL/PostgreSQL** - Primary database for card storage
- **Redis** - Caching layer for performance optimization
- **Cardmarket API** - External pricing data integration

### Frontend
- **Vue.js 3** - Reactive single-page application
- **Vue Router 4** - Client-side routing
- **Pinia** - State management
- **Tailwind CSS 3** - Utility-first styling framework

### Build Tools
- **Vite** - Fast build tool and development server
- **Laravel Mix** - Asset compilation

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Node.js 16+
- Composer
- MySQL/PostgreSQL
- Redis (optional, for caching)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/sallarrabiei/MTGplayer.git
   cd MTGplayer
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mtgplayer
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   CACHE_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379

   # MTG Data Source
   MTG_CARDS_JSON_URL=https://mtgjson.com/api/v5/AllCards.json

   # Cardmarket API (optional)
   CARDMARKET_APP_TOKEN=your_app_token
   CARDMARKET_APP_SECRET=your_app_secret
   CARDMARKET_ACCESS_TOKEN=your_access_token
   CARDMARKET_ACCESS_SECRET=your_access_secret
   ```

6. **Database setup**
   ```bash
   php artisan migrate
   ```

7. **Import card data**
   ```bash
   php artisan mtg:import-cards
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to see the application!

## 📖 API Documentation

### Card Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/cards` | Search and filter cards |
| `GET` | `/api/cards/{id}` | Get specific card details |
| `GET` | `/api/cards/{id}/price` | Get card pricing from Cardmarket |
| `GET` | `/api/cards/sets` | Get all available sets |
| `GET` | `/api/cards/stats` | Get database statistics |

### Search Parameters

- `name` - Search by card name (partial match)
- `set` - Filter by set code
- `rarity` - Filter by rarity (common, uncommon, rare, mythic)
- `colors` - Filter by colors (array: W, U, B, R, G)
- `cmc` - Filter by converted mana cost
- `type` - Filter by card type
- `page` - Pagination page number
- `per_page` - Results per page (max 100)

### Example Requests

```bash
# Search for Lightning cards
GET /api/cards?name=Lightning

# Get rare cards from Zendikar Rising
GET /api/cards?set=ZNR&rarity=rare

# Find red and green cards with CMC 3
GET /api/cards?colors[]=R&colors[]=G&cmc=3
```

## 🎯 Usage Guide

### Importing Card Data

The application includes a powerful import command to fetch and process MTG card data:

```bash
# Import with default settings
php artisan mtg:import-cards

# Import from custom URL
php artisan mtg:import-cards --url=https://custom-url.com/cards.json

# Import with custom batch size
php artisan mtg:import-cards --batch-size=1000

# Force import without confirmation
php artisan mtg:import-cards --force
```

### Cardmarket API Integration

To enable real-time pricing, configure your Cardmarket API credentials:

1. Register at [Cardmarket Developer Portal](https://www.cardmarket.com/en/Magic/API)
2. Create a new application
3. Add your credentials to `.env`
4. Pricing will automatically appear on card details and search results

### Caching Configuration

The application uses multi-layer caching for optimal performance:

- **Database queries**: 5-30 minutes
- **API responses**: 30 minutes
- **Card statistics**: 30 minutes
- **Set data**: 1 hour

Configure cache durations in `config/mtg.php`.

## 🧪 Testing

Run the test suite to ensure everything works correctly:

```bash
# Run all tests
php artisan test

# Run specific test types
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## 🎨 Customization

### MTG-Themed Colors

The application includes custom Tailwind CSS classes for MTG colors:

```css
.mana-white    /* White mana */
.mana-blue     /* Blue mana */
.mana-black    /* Black mana */
.mana-red      /* Red mana */
.mana-green    /* Green mana */

.rarity-common    /* Common rarity */
.rarity-uncommon  /* Uncommon rarity */
.rarity-rare      /* Rare rarity */
.rarity-mythic    /* Mythic rare rarity */
```

### Component Structure

```
resources/js/
├── components/
│   ├── CardComponent.vue     # Individual card display
│   └── ManaCost.vue         # Mana cost symbols
├── views/
│   ├── Home.vue             # Main search page
│   ├── CardDetail.vue       # Card details page
│   ├── Favorites.vue        # User favorites
│   └── Statistics.vue       # Database statistics
├── stores/
│   ├── app.js              # Global app state
│   └── cards.js            # Card-related state
└── router/
    └── index.js            # Vue Router configuration
```

## 🚀 Deployment

### Production Build

```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables

Ensure these are set in production:

```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Recommended Hosting

- **Vercel** - For frontend deployment
- **Heroku** - Full-stack deployment
- **AWS** - Scalable cloud deployment
- **DigitalOcean** - VPS deployment

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make your changes**
4. **Add tests** for new functionality
5. **Run the test suite**
   ```bash
   php artisan test
   npm run test
   ```
6. **Commit your changes**
   ```bash
   git commit -m 'Add amazing feature'
   ```
7. **Push to your branch**
   ```bash
   git push origin feature/amazing-feature
   ```
8. **Open a Pull Request**

### Code Style

- Follow PSR-12 for PHP code
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Follow Vue.js style guide for frontend code
- Use meaningful commit messages

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- **[MTGJSON](https://mtgjson.com)** - Comprehensive MTG card data
- **[Cardmarket](https://www.cardmarket.com)** - Real-time pricing data
- **[Laravel](https://laravel.com)** - Elegant PHP framework
- **[Vue.js](https://vuejs.org)** - Progressive JavaScript framework
- **[Tailwind CSS](https://tailwindcss.com)** - Utility-first CSS framework

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/sallarrabiei/MTGplayer/issues)
- **Discussions**: [GitHub Discussions](https://github.com/sallarrabiei/MTGplayer/discussions)
- **Documentation**: [Wiki](https://github.com/sallarrabiei/MTGplayer/wiki)

---

**Built with ❤️ for the Magic: The Gathering community**
