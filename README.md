# Magic: The Gathering Search Engine

The Magic: The Gathering Search Engine is a web application for searching and filtering Magic: The Gathering (MTG) cards. It is built with Laravel, Vue.js, and Tailwind CSS, and provides a fast, responsive, and intuitive interface using local JSON data.

## Features

- **Card Search**: Search for cards by name, type, set, or rarity.
- **Advanced Filters**: Filter by color, mana cost, keywords, and more.
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices.
- **Local Data**: Loads MTG card data from a local JSON file.
- **Dynamic UI**: Built as a single-page application with Vue.js.
- **Favorites (Optional)**: Logged-in users can save their favorite cards.

> **Note**: This project uses a local JSON file for card data, ensuring offline access and high performance.

## Tech Stack

- **Backend**: Laravel (API and server-side logic)
- **Frontend**: Vue.js (component-based UI)
- **Styling**: Tailwind CSS (utility-first CSS framework)
- **Database**: MySQL or PostgreSQL (optional, used for storing favorites)
- **Data Source**: Local JSON file

## Usage

1. Use the search bar to look for cards by name or attribute.
2. Apply filters such as set, color, or mana cost using dropdown menus.
3. Browse through a responsive, MTG-themed interface styled with Tailwind CSS.

## Development

- **Backend**:
  - Laravel manages API routes such as `/api/cards`.
  - Parses and serves data from the local JSON file.
  - You can cache parsed JSON using Redis to improve performance.

- **Frontend**:
  - Vue components are located in `resources/js/components/`.
  - Vue Router is used to create a seamless single-page experience.

- **Styling**:
  - Tailwind CSS is configured in `resources/css/app.css`.
  - MTG-themed color schemes (e.g., blue, red, green) can be customized there.

## Contributing

To contribute:

1. Fork the repository at [https://github.com/sallarrabiei/MTGplayer](https://github.com/sallarrabiei/MTGplayer).
2. Create a new branch:
