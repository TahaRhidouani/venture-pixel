# Venture Pixel

### Main features:

- Gorgeous responsive and minimal UI that also works with mobile devices
- Support for credit card, Apple Pay & Google Pay transactions
- A login interface for users to see their pictures and order
- A admin interface for the administrator to manage orders and upload pictures for clients
- Multi language website, that allows users to switch language without refreshing the page

<br />

**A beautiful modern UI that attracts customers**

![home](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_1.gif?raw=true)

<br />

**Useful navigation page with reviews that also allows for language selection**

![booking](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_4.png?raw=true)

<br />

**Modern booking form**

![booking](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_2.png?raw=true)

<br />

**Stunning personalised order page where users can pick pictures they want to buy**

![order](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_5.gif?raw=true)

<br />

**Clean admin interface that displays order details**

![order](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_3.png?raw=true)

<br />

**Thorough interface that allows the admin to upload clients images**

![order](https://github.com/TahaInc/venture-pixel/blob/master/images/screenshot_6.png?raw=true)

## Run

Every push to `main` builds `ghcr.io/taharhidouani/venture-pixel` (tags `main` and `sha-<commit>`) on GitHub Actions. The image serves the site with FrankenPHP on port 8080 and answers `GET /healthz`.

```sh
docker build -t venture-pixel .
docker run --rm -p 8080:8080 -v venture-pixel-data:/data -e ADMIN_PASSWORD=change-me venture-pixel
```

Bookings, pictures, orders and sessions are written to `/data`, so keep it on a volume. Requests to `data/...` go through `data.php`, which only serves a booking to the admin or to that booking's customer.

| Variable | Purpose |
| --- | --- |
| `ADMIN_PASSWORD` | Admin sign-in on the login page. The admin is disabled when unset. |
| `STRIPE_SECRET_KEY` | Stripe secret key for card payments (the publishable key is in `js/payment.js`). |
| `SQUARE_ACCESS_TOKEN` | Square API token used by the booking webhook (`square.php`). |
| `SQUARE_WEBHOOK_SIGNATURE_KEY`, `SQUARE_WEBHOOK_URL` | Check the Square webhook signature. The webhook rejects every call when unset. |
| `ADMIN_EMAIL` | Receives new-booking notifications. None are sent when unset. |
| `DATA_DIR` | Storage directory, `/data` by default. |

Emails use PHP `mail()`, which needs a mail transport the image does not include.
