SNEAKERS ROOM
Online Sneaker Shop System

Group project for IS105, Section - 3C

ABOUT THE SYSTEM

Sneakers Room is an online shoe shop made with PHP, MySQL, HTML, CSS and JavaScript. It has two sides. The customer side lets people browse the shoes, choose a size and color, add shoes to a cart, place an order and follow their orders. The admin side lets the store owner manage the stock, orders and sales, and see a dashboard with graphs.

FEATURES FOR CUSTOMERS

- Register and log in. Customers and admins use the same login page, and the account type decides which page opens after logging in.
- Shop page with brand tabs, a search box and sorting. Sold-out shoes are shown last.
- Product page with a photo that zooms when the mouse is over it, color choices, and a size grid. Sizes that are out of stock are shown but cannot be bought.
- A "You may also like" row of other shoes on the product page.
- Cart where the customer can add shoes, change the quantity and remove items. All items in the cart can be ordered at once, or one pair can be ordered right away with Order now.
- Payment choices: GCash, Maya and Cash on Delivery (J&T).
- My Orders page that shows each order with the photo of the shoe, its color, size, payment status and delivery status.
- Cancel order with a reason. This is only allowed while the order is unpaid and still being prepared, and the pairs go back into stock.


FEATURES FOR ADMINS

- Dashboard with sales cards, a bar graph of the last 7 days, a chart of sales by payment method, order progress, best sellers, stock by brand, stock alerts and recent activity.
- Stock page where the admin can add a shoe with its color, photo and several sizes at once, add sizes to a shoe that is already in stock, change the quantity and price, and remove a shoe.
- New Order page for recording walk-in or online orders.
- Payouts page for marking orders as paid and updating the delivery status (Preparing, At J&T hub, In transit, Delivered).
- A red notice on every admin page when a customer cancels an order.
- Sales page, Sold Out page with a quick restock button, Activity Log that shows who did what, and an Admins page for adding or removing admin accounts.


TECHNOLOGIES USED

- PHP 8 with PDO and sessions
- MySQL / MariaDB
- HTML, CSS and JavaScript
- XAMPP (Apache and MySQL) for running it on a computer


HOW TO RUN IT

1. Install XAMPP, then start Apache and MySQL in the XAMPP Control Panel.
2. Copy the project folder into C:\xampp\htdocs\ and name it sneakersroom. The address will then be http://localhost/sneakersroom/
3. Open phpMyAdmin at http://localhost/phpmyadmin and create a new database named sneakers_db.
4. Click the sneakers_db database, choose Import, and select the file database/sneakers_db.sql from this project.
5. Open includes/db.php and check the database settings. The default is host localhost, user root, no password, database sneakers_db.
6. Open http://localhost/sneakersroom/ in a browser.

When the first page is opened, the system adds a few columns and a table by itself if they are missing (shoe color and photo, the cancelled order details and the cart table).


ACCOUNTS FOR TESTING

Admin account
  Email: [sneakersroom.admin2@gmail.com]
  Password: [12345678]

Customer account
  Email: [customer00@gmail.com]
  Password: [12345876]

Everyone logs in on the same page: http://localhost/sneakersroom/login.php
Admins are taken to the dashboard and customers are taken to the shop.


FOLDERS AND FILES

index.php          the home and shop page
login.php, register.php, logout.php
assets/            style.css and script.js
includes/          database connection, shared functions, page headers and footers
customer/          product.php, cart.php, orders.php
admin/             dashboard.php, index.php (stock), order_new.php, payouts.php, sales.php, soldout.php, activity.php, admins.php
uploads/           the product photos
database/          sneakers_db.sql (the exported database)


NOTES

This project was made for school and is meant to run on a local computer. Before putting it online, the database password should be changed, admin_setup.php should be removed, and the site should use HTTPS.

Passwords are saved in hashed form using password_hash(). The login is limited to 5 wrong tries per minute.
