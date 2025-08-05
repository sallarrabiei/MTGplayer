# MTG Search Engine

A modern, responsive Magic: The Gathering card search engine built with Laravel, Vue.js, and Tailwind CSS. This application allows users to search and filter MTG cards, import data from JSON URLs, and fetch prices from the Cardmarket API.

## 🚀 Features

### Core Functionality
- **Card Search**: Debounced search with real-time results
- **Advanced Filtering**: Filter by set, rarity, color, type, mana cost, and more
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile
- **Card Details**: Detailed view with images, oracle text, and pricing information
- **Price Integration**: Fetch and display current market prices from Cardmarket API

### Technical Features
- **RESTful API**: Clean, documented API endpoints
- **Database Import**: Import card data from JSON URLs
- **Caching**: Redis/Laravel caching for performance
- **Rate Limiting**: Respects Cardmarket API rate limits
- **Error Handling**: Comprehensive error handling and logging

## 🛠 Tech Stack

### Backend
- **Laravel 11**: PHP framework for API and backend logic
- **SQLite**: Database (configurable for MySQL/PostgreSQL)
- **Eloquent ORM**: Database interactions
- **Artisan Commands**: Data import and management tools

### Frontend
- **Vue.js 3**: Progressive JavaScript framework
- **Vue Router**: Client-side routing
- **Pinia**: State management
- **Tailwind CSS**: Utility-first CSS framework

### External APIs
- **Cardmarket API**: Price data and card information
- **Scryfall**: Card images and metadata

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd mtg-search-engine
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

5. **Database setup**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## 🔧 Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Cardmarket API (optional)
CARDMARKET_APP_TOKEN=your_app_token
CARDMARKET_APP_SECRET=your_app_secret
CARDMARKET_ACCESS_TOKEN=your_access_token
CARDMARKET_ACCESS_TOKEN_SECRET=your_access_token_secret
```

### Cardmarket API Setup

1. Register at [Cardmarket](https://www.cardmarket.com/)
2. Create an application in the developer portal
3. Add your credentials to the `.env` file

## 📊 Data Import

### Import Sample Data
```bash
php artisan cards:sample
```

### Import from JSON URL
```bash
php artisan cards:import "https://example.com/cards.json"
```

### Import with Custom Chunk Size
```bash
php artisan cards:import "https://example.com/cards.json" --chunk=500
```

## 🚀 API Endpoints

### Cards
- `GET /api/cards` - List cards with search and filters
- `GET /api/cards/{id}` - Get specific card details
- `GET /api/cards/cardmarket/{id}` - Get card by Cardmarket ID
- `POST /api/cards/{id}/update-prices` - Update card prices

### Filters
- `GET /api/cards/sets` - Get available sets
- `GET /api/cards/rarities` - Get available rarities
- `GET /api/cards/colors` - Get available colors

### Query Parameters

#### Search
- `name` - Search by card name
- `set` - Filter by set code
- `rarity` - Filter by rarity
- `color` - Filter by color
- `type` - Filter by type line
- `cmc` - Filter by converted mana cost
- `power` - Filter by power
- `toughness` - Filter by toughness
- `with_prices` - Include price data

#### Pagination
- `per_page` - Items per page (max 100)
- `page` - Page number

## 🎨 Frontend Components

### CardSearch.vue
Main search interface with:
- Debounced search input
- Advanced filters
- Responsive card grid
- Pagination

### CardDetail.vue
Detailed card view with:
- Card images
- Oracle text
- Pricing information
- Card attributes

## 🗄 Database Schema

### Cards Table
- `id` - Primary key
- `name` - Card name
- `set_name` - Set name
- `set_code` - Set code
- `rarity` - Card rarity
- `type_line` - Card type
- `oracle_text` - Oracle text
- `mana_cost` - Mana cost
- `cmc` - Converted mana cost
- `colors` - Card colors (JSON)
- `cardmarket_id` - Cardmarket ID
- `scryfall_id` - Scryfall ID
- `image_url` - Card image URL
- And many more fields...

### Card Prices Table
- `id` - Primary key
- `card_id` - Foreign key to cards
- `cardmarket_id` - Cardmarket ID
- `price_type` - Price type (low, avg, high, market)
- `price` - Price value
- `currency` - Currency code
- `condition` - Card condition
- `foil` - Foil status
- `available_quantity` - Available quantity
- `price_updated_at` - Last price update

## 🧪 Testing

### Backend Tests
```bash
php artisan test
```

### Frontend Tests
```bash
npm run test
```

## 🚀 Deployment

### Vercel (Frontend)
1. Build the frontend assets
2. Deploy to Vercel
3. Configure environment variables

### Heroku (Backend)
1. Create Heroku app
2. Set buildpacks for PHP and Node.js
3. Configure environment variables
4. Deploy

### AWS (Full Stack)
1. Set up EC2 instance
2. Configure Nginx/Apache
3. Set up SSL certificates
4. Deploy application

## 📝 Development

### Adding New Features
1. Create feature branch
2. Implement changes
3. Add tests
4. Update documentation
5. Submit pull request

### Code Style
- Follow PSR-12 for PHP
- Use ESLint for JavaScript
- Follow Vue.js style guide

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 🙏 Acknowledgments

- [Cardmarket](https://www.cardmarket.com/) for the API
- [Scryfall](https://scryfall.com/) for card data
- [Laravel](https://laravel.com/) for the backend framework
- [Vue.js](https://vuejs.org/) for the frontend framework
- [Tailwind CSS](https://tailwindcss.com/) for styling

## 📞 Support

For support, please open an issue on GitHub or contact the development team.

---

**Note**: This application respects Cardmarket API rate limits and terms of service. Please ensure compliance when using the Cardmarket integration.
