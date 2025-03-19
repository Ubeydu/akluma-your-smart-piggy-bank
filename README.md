# Piggy Bank Savings Application

A web application built with Laravel that helps users create piggy banks to save money for products they want to purchase.

## About The Project

This application allows users to:
- Create virtual piggy banks for saving towards specific goals
- Track savings progress visually
- Set target dates and amounts
- Monitor current balances and progress

## Tech Stack

- **Framework**: Laravel 12
- **Frontend**: Blade templates, Tailwind CSS
- **JavaScript**: Vanilla JS, Alpine.js
- **Database**: MySQL
- **PHP Version**: 8.4.4

## Installation

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Start the Docker environment: `./vendor/bin/sail up -d`
    - If this is your first time installing, run `docker run --rm -v $(pwd):/app composer install` first
4. Run `./vendor/bin/sail artisan key:generate`
5. Run `./vendor/bin/sail artisan migrate`
6. Run `./vendor/bin/sail npm install && ./vendor/bin/sail npm run build`
7. Access the application at http://localhost

## License

[MIT License](LICENSE)

This README now includes:
1. A basic project description
2. Tech stack information
3. Your detailed browser navigation handling documentation
4. Basic installation instructions
5. A license reference

You can expand this with more sections as your project grows, such as:
- Screenshots
- API documentation
- Deployment instructions
- Testing information
- Contribution guidelines

## Development Notes

### Handling Browser Navigation Flash Data and UI State Persistence

#### Problem
When a user creates a new item and is redirected to a list page with success message and highlighted item, using browser back/forward navigation can cause these visual elements to reappear incorrectly.

#### Solution Components

##### Server-Side (Controller)
- Added timestamp to flash session in `PiggyBankCreateController@storePiggyBank`:
  ```php
  return redirect()
      ->route('piggy-banks.index')
      ->with('newPiggyBankId', $piggyBank->id)
      ->with('newPiggyBankCreatedTime', time())
      ->with('success', __('Your piggy bank has been created successfully.'));


- Ensured session clearing in `PiggyBankController@index`:
  ```php
  $newPiggyBankId = session('newPiggyBankId');
  $newPiggyBankCreatedTime = session('newPiggyBankCreatedTime');
  
  session()->forget(['newPiggyBankId', 'newPiggyBankCreatedTime']);
  ```

##### Component Markup (piggy-bank-card.blade.php)
- Added data attributes to store IDs and timestamps:
  ```php
  @props(['piggyBank', 'newPiggyBankId' => null, 'newPiggyBankCreatedTime' => null])
  
  <div class="... piggy-bank-card"
       data-piggy-bank-id="{{ $piggyBank->id }}" 
       data-new-piggy-bank-id="{{ $newPiggyBankId }}"
       data-new-piggy-bank-time="{{ $newPiggyBankCreatedTime }}">
  ```

##### Client-Side (resources/js/piggy-bank-highlight.js)
- Created dedicated JS file with two main functions:
    1. Track highlighted elements using localStorage
    2. Detect and handle browser navigation events

- Key code for cards:
  ```javascript
  const storageKey = 'highlighted_piggy_bank_' + newPiggyBankId;
  const hasBeenHighlighted = localStorage.getItem(storageKey);
  
  if (!hasBeenHighlighted) {
      card.classList.add('highlight-new', 'border-indigo-500', 'ring-2', 'ring-indigo-200');
      localStorage.setItem(storageKey, 'true');
  } else {
      card.classList.remove('highlight-new', 'border-indigo-500', 'ring-2', 'ring-indigo-200');
  }
  ```

- Key code for Alpine.js flash messages:
  ```javascript
  const successContainer = document.querySelector('[x-data*="show: true"]');
  if (successContainer) {
      const navigationType = performance.getEntriesByType('navigation')[0].type;
      if (navigationType === 'back_forward') {
          if (window.Alpine && successContainer.__x) {
              successContainer.__x.setData('show', false);
          } else {
              successContainer.style.display = 'none';
          }
      }
  }
  ```

#### How It Works
1. Server generates flash data and ID for the newly created item
2. JavaScript checks if item was already highlighted using localStorage
3. For success messages, detects back/forward navigation using Performance API
4. For Alpine.js components, interacts with the data model instead of just hiding elements

#### Future Reference
When implementing redirects with flash messages or highlighted items:
- Always use flash session data (->with()) instead of regular session
- Consider tracking UI state in localStorage/sessionStorage for persistence across page loads
- Use data attributes to pass IDs and timestamps to JavaScript
- Use the Performance API to detect navigation types (performance.getEntriesByType('navigation')[0].type)
- When working with UI frameworks like Alpine.js, interact with their data model rather than manipulating the DOM directly

#### Files Modified
- `app/Http/Controllers/PiggyBankCreateController.php` (add timestamp to session)
- `app/Http/Controllers/PiggyBankController.php` (retrieve and clear session)
- `resources/views/components/piggy-bank-card.blade.php` (add data attributes)
- `resources/js/piggy-bank-highlight.js` (create new file)
- `resources/views/piggy-banks/index.blade.php` (include JS file)
