# EasyPeasy Background Job Runner

EasyPeasy is a custom background job runner built with Laravel. It allows you to run PHP classes as independent jobs, bypassing Laravel's built-in queue system. This project focuses on flexibility, error handling, and providing advanced features for monitoring and managing background jobs.

---

## Features

- **Custom Job Runner:** Execute PHP classes as standalone jobs.
- **Retry Mechanism:** Automatically retry failed jobs based on configurable logic.
- **Timeout Support:** Handle long-running jobs with customizable timeouts.
- **Prioritization:** Assign priorities to jobs for controlled execution order.
- **Logging:** Comprehensive logging for job dispatch, retries, and completion.
- **Extensible Design:** Add more features, such as a web-based dashboard, with ease.

---

## Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 9.x or higher
- **Composer**: Latest version
- **Database**: MySQL, PostgreSQL, or any database supported by Laravel

---

## Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/KoketsoMabuela92/easypeasy.git
   cd easypeasy

2. **Install Dependencies**:
   ```bash
   composer install


3. **Setup Up Environment: Create a `.env` file in the project root**:
   ```bash
   cp .env.example .env
Update the `.env` file with your database and other configurations.

4. **Generate Application Key**:
   ```bash
   php artisan key:generate

5. **Run Migrations**:
   ```bash
   php artisan migrate

---
## Usage

1. **Running Jobs**

You can create and dispatch custom jobs using the runBackgroundJob method.

Example:

```bash
    use App\Jobs\GenerateReportJob;
    
    // Dispatch a simple job
    php artisan job:run "App\Jobs\GenerateReportJob" handle
    
    // Dispatch a job with parameters
    php artisan job:run "App\Jobs\GenerateReportJob" handle [] 1 3 5
    
    // Dispatch a job with retry logic
    php artisan job:run "App\Jobs\GenerateReportJob" handle [] 1 3 5
```

2. **Logging**

   Logs for dispatched jobs, retries, and completions are available in the Laravel log files (storage/logs/laravel.log) & (storage/logs/background_jobs_status.log) and also for job errors, in (storage/logs/background_jobs_errors.log).



3. **Web Interface**

Execute the below artisan command, then visit `http://127.0.0.1:8000/dashboard` for view the jobs and their statuses, cancel running jobs, and also retry failed jobs.
```bash
    php artisan serve
```

---
## Running Tests

**Pre-requisite**

Ensure PHPUnit is installed via Composer:
```bash
    composer require --dev phpunit/phpunit
```

**Execute Tests**

Run the included unit tests to verify functionality:
```bash
    php artisan test
```

---

## Roadmap

**Future Enhancements:**

* Support for real-time notifications on job status.
* Integration with external job management tools.
* Expand test coverage for edge cases.
* Add Laravel Pint, for linting and code quality management.


---

## Contributing

I happily :-) welcome contributions to EasyPeasy Background Job Trigger! Here's how you can help:

1. Fork the repository.
2. Create a feature branch:
```bash
    git checkout -b feature/your-feature-name
```
3. Commit your changes:
```bash
    git commit -m "Add your descriptive commit message"
```
4. Push to your branch:
```bash
    git push origin feature/your-feature-name
```
5. Open a pull request on GitHub :-)


---

## License
This project is licensed under the MIT License. See the [LICENSE](https://opensource.org/license/mit) file for details.


---

## Contact
For questions or support, feel free to reach out to:
* Author: Koketso Mabuela
* Email: glenton9@gmail.com
* GitHub: KoketsoMabuela92


---

Enjoy building with EasyPeasy! 🚀
