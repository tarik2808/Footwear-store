# Footwear Store

A full-stack e-commerce application for selling footwear products.

## Live Demo
[Footwear Store](https://your-deployment-url.com)

## Features
- User authentication and authorization
- Product browsing and searching
- Shopping cart functionality
- Order management
- Admin dashboard
- Responsive design

## Tech Stack
- Frontend: HTML, CSS, JavaScript
- Backend: PHP (FlightPHP)
- Database: MySQL
- Authentication: JWT

## Project Structure
```
├── frontend/
│   ├── assets/
│   ├── pages/
│   ├── services/
│   └── utils/
├── backend/
│   ├── controllers/
│   ├── dao/
│   ├── routes/
│   ├── services/
│   └── docs/
```

## Setup Instructions

1. Clone the repository:
```bash
git clone https://github.com/your-username/footwear-store.git
```

2. Set up the database:
- Import the `backend/shopdb.sql` file into your MySQL database
- Update database credentials in `backend/config.php`

3. Configure the backend:
- Install PHP dependencies
- Set up your web server (Apache/Nginx) to point to the backend directory
- Configure CORS settings if needed

4. Configure the frontend:
- Update the API base URL in all service files
- Serve the frontend files using a web server

## API Documentation
API documentation is available at `/api/docs` when running the application locally.

## Security Features
- JWT-based authentication
- Password hashing
- Input validation and sanitization
- SQL injection prevention
- XSS protection

## Contributing
1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License
This project is licensed under the MIT License.