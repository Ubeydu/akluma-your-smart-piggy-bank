# Preventing Lost Registrations: Options

**Date:** 2026-02-14
**Context:** Investigation into user Paula Nakato (ID 9) who registered on Jan 29, 2026 but never verified her email. 10+ verification emails were delivered to Gmail but never opened (likely spam-filtered). She hit resend repeatedly and gave up.
**Related issues:** #305, #306, #307

---

## The Real Problem

This isn't an email deliverability bug. The system worked correctly. The problem is architectural: **you have a single point of failure (email inbox placement) between a motivated user and your product.** At your stage, every lost registration is expensive.

Paula Nakato wanted to use Akluma. She registered. She hit resend 10+ times. She was motivated. And you lost her anyway because Gmail put your email in spam. The question is: how do you make sure this never happens again?

---

## Option 1: Fix the Verification Screen UX (Quick Win)

**Effort:** A few hours | **Impact:** Moderate | **Users saved:** Some
**GitHub issue:** #305

Right now, your verification screen says:

> "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?"

That's it. A motivated user whose email is in spam has no guidance. Changes:

- Add "Check your spam/junk folder" prominently -- not as a footnote, as the second thing they read
- Show the actual email address they registered with (so they know which inbox to check)
- Add a rate-limited resend with a visible cooldown timer (prevents the 10-resends-in-2-minutes frustration)
- Add "Wrong email? Update it" option (inline form to change email for users who registered with a typo)
- Add "Still not working? Contact us at contact@akluma.com"
- Also includes password reset throttling fix (supersedes #157 and #219)

**Honest assessment:** This helps, but it's a band-aid. Users who don't read instructions (most users) will still bounce.

---

## Option 2: Delayed/Soft Verification (High Impact, Moderate Effort)

**Effort:** 1-2 days | **Impact:** High | **Users saved:** Most
**GitHub issue:** #306

**The idea:** Let users into the app immediately after registration. Don't block them at the door. Instead:

- After registration, redirect straight to the dashboard
- Show a persistent but non-blocking banner: "Verify your email to make sure your savings reminders reach you."
- No feature gates -- unverified users can do everything (create piggy banks, set up schedules, receive reminders)
- Send the verification email in the background
- Optionally send a reminder email after 24h, 3 days if still unverified

**Why this is powerful:** Paula Nakato would have gotten into the app, set up her first piggy bank, experienced the value of Akluma, and THEN had a reason to go dig through her spam folder to verify. You're flipping the order: **value first, verification second.**

**Why no feature gates:** Akluma is a relatively simple savings app. There are no features that genuinely require a verified email to function safely. The only real reason verification matters is to ensure reminder emails reach the user -- and that's a reason to *encourage* verification, not to *enforce* it as a gate.

**Many successful products do this:** Notion, Slack, and most modern SaaS apps let you in first and nag you to verify later.

**Risk:** Spam account registrations. But at your scale (user #9), this is a non-issue. You can always add verification gates later if abuse happens.

---

## Option 3: Sign in with Google (High Impact, Moderate Effort)

**Effort:** 2-3 days | **Impact:** Very High | **Users saved:** Most Gmail users
**GitHub issue:** #307

**The idea:** Add a "Sign in with Google" button using Laravel Socialite. When a user signs in with Google:

- Google has already verified their email
- No verification email needed at all
- One-click registration (no form to fill out)
- You get their name and verified email from Google's OAuth response

**Why this matters for your specific case:** Paula Nakato uses Gmail. If "Sign in with Google" existed, she would have clicked one button and been inside the app in 3 seconds. No email, no spam folder, no friction.

**Market consideration:** Google accounts are nearly universal. Even in African markets where Akluma has users, Gmail/Google is dominant on Android devices. You could add Apple Sign-In later for iOS users.

**This also improves your conversion funnel beyond just verification:** Filling out a registration form (name, email, password, confirm password, two checkboxes) is itself a friction point. Social login reduces registration to one click.

---

## Option 4: SMS/OTP Verification (Different Channel) -- Future

**Effort:** 3-5 days + ongoing cost | **Impact:** Very High | **Users saved:** Nearly all

**The idea:** Send a 6-digit code via SMS instead of (or in addition to) an email link. User types the code into your app.

**Why it's interesting for Akluma:**

- Your notification_preferences already have SMS as a channel (with "Coming Soon" badge)
- In African markets (where you have users like Paula in Nairobi), SMS is often more reliable than email
- Phone numbers are the primary digital identity in many African countries
- SMS doesn't have a spam folder

**Providers to consider:**

- Africa's Talking -- specifically built for African markets, cheaper per-SMS
- Twilio -- global, well-documented, Laravel packages available
- Vonage -- competitive pricing

**Cost:** Roughly $0.01-0.05 per SMS depending on country. At your scale, negligible.

**Downsides:** Requires collecting phone number (adds a field to registration), SMS costs scale linearly, and you need to handle phone number format validation across countries.

---

## Option 5: WhatsApp Verification -- Future

**Effort:** 1-2 weeks | **Impact:** High in target markets

Similar to SMS but through WhatsApp Business API. Very high penetration in Africa. More expensive to set up (WhatsApp Business API approval process), but messages are free for the first 1,000/month in some tiers. Worth considering later if your African user base grows, but probably premature right now.

---

## Chosen Approach: Options 1 + 2 + 3

**Do all three. They solve different parts of the problem and complement each other.**

- **Option 2 (Delayed verification)** is your safety net. It catches everyone -- regardless of email provider, spam filters, or device. It's also a UX philosophy shift that makes your product feel more modern and welcoming.
- **Option 3 (Google Sign-In)** eliminates friction entirely for the majority of users. It's the modern standard. Users expect it.
- **Option 1 (Better verification screen)** -- a quick improvement for users who still need to verify via email.

**Implementation order:**

1. **#306 (Option 2) first** -- immediate safety net, no new dependencies
2. **#305 (Option 1) next** -- improve the verification and password reset screens
3. **#307 (Option 3) last** -- add Google Sign-In (requires external setup: Google Cloud Console, Socialite, Fly secrets)
4. **Options 4/5 later** -- when you have enough users to justify the SMS/WhatsApp infrastructure
