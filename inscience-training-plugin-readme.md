# InScience Training – WordPress Plugin

A WordPress plugin that manages course enrolments for InScience Ltd (https://www.inscience.co.nz/).

---

## Features

### 🗓 Visual Calendar
- Full-page FullCalendar (month/list view) powered by [FullCalendar v6](https://fullcalendar.io/).
- Each event shows course type (CLASSROOM / ZOOM), topic, unit standard codes, date, location/city and enrolment status.
- Colour-coded: **Navy** = Classroom, **Green** = Zoom.
- Click any event to open a detail modal with an **Enrol Now** button.
- Shortcode: `[inscience_calendar]`

### 📝 Enrolment Form
Complete NZQA-compliant enrolment form with all required fields:
- Course selection (upcoming courses only)
- New / refresher registration type
- Employer & branch details
- Group organiser email
- Attendee: given names, last name, preferred name, residential address, email, DOB, phone
- Ethnic group (multi-select, for NZQA statistical purposes)
- Gender
- Payment method (Stripe / Bank Transfer / On Account)
- Declaration checkbox
- Shortcode: `[inscience_enrolment_form]` or `[inscience_enrolment_form course_id="123"]`

### 💳 Payment Options
1. **Stripe** – redirects to Stripe Checkout. Webhook automatically updates enrolment on payment completion.
2. **Bank Transfer** – bank details shown in confirmation email.
3. **On Account** – for pre-approved account holders.

### 🔔 New Course Notification Sign-up
Let visitors sign up to receive an email when new courses are added.
- Shortcode: `[inscience_notification_signup]`
- Unsubscribe link included in every notification email.

### ⚙️ Admin Dashboard
Located under **InScience Training** in the WordPress admin sidebar:

| Page | Purpose |
|------|---------|
| Dashboard | Stats overview: active courses, total/pending enrolments, subscribers |
| Add New Course | Create or edit a course (title, description, type, date, city, capacity, price, US codes) |
| Current Courses | List all courses with enrolment counts; edit or delete |
| Enrolments | Filter/search enrolments; view full enrolment details; update status & payment; add admin notes |
| Emails | Edit all email templates (subject + body) with placeholder support |
| Settings | Stripe keys, bank transfer details, email from name/address/logo, enrolment form page |

---

## Installation

1. Copy the `inscience-training` folder to your WordPress `wp-content/plugins/` directory.
2. Activate the plugin from **Plugins** → **Installed Plugins**.
3. The plugin will automatically create the required database tables on activation.
4. Go to **InScience Training → Settings** and configure:
   - Stripe API keys (publishable key, secret key, webhook secret)
   - Bank transfer details
   - Email from name / address
   - Set the **Enrolment Form Page** to the page containing `[inscience_enrolment_form]`
5. Create a page for the calendar with `[inscience_calendar]`.
6. Create a page for the enrolment form with `[inscience_enrolment_form]`.
7. (Optional) Create a page for course notifications with `[inscience_notification_signup]`.

---

## Stripe Webhook

After enabling Stripe:
1. In your Stripe Dashboard, add a webhook endpoint pointing to:
   `https://your-site.com/wp-admin/admin-ajax.php?action=inscience_stripe_webhook`
2. Set the webhook to listen for the `checkout.session.completed` event.
3. Copy the **Webhook Signing Secret** (starts with `whsec_`) into InScience Training → Settings.

---

## Email Templates

All emails are editable via **InScience Training → Emails**. Available templates:

| Slug | Sent When |
|------|-----------|
| `enrolment_confirmation` | Attendee submits the enrolment form |
| `enrolment_admin` | Admin notified of new enrolment |
| `payment_received` | Stripe payment confirmed via webhook |
| `new_course_notification` | A course is published (sent to subscribers) |

### Available Placeholders
`{given_names}`, `{last_name}`, `{course_title}`, `{course_date}`, `{course_type}`,
`{course_location}`, `{enrolment_id}`, `{payment_method}`, `{payment_instructions}`,
`{email}`, `{phone}`, `{employer}`, `{admin_url}`, `{enrol_url}`, `{unsubscribe_url}`, `{name}`

---

## Shortcodes

| Shortcode | Description |
|-----------|-------------|
| `[inscience_calendar]` | Visual FullCalendar of upcoming courses |
| `[inscience_enrolment_form]` | Full enrolment form (with payment) |
| `[inscience_enrolment_form course_id="123"]` | Pre-select a specific course |
| `[inscience_notification_signup]` | Sign-up form for new course notifications |

---

## Requirements

- WordPress 5.9+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- An HTTPS site (required for Stripe)

---

## Security

- All form submissions are protected by WordPress nonces.
- Forms are compatible with Cloudflare protection.
- All database queries use `$wpdb->prepare()`.
- Stripe webhook signatures are verified using HMAC-SHA256.
- Input is sanitized with appropriate WordPress sanitization functions.
- Output is escaped with `esc_html()`, `esc_attr()`, `esc_url()` throughout.

---

## Changelog

### 1.0.0
- Initial release.
