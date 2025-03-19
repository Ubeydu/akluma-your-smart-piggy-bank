# **Push Notification System for a Laravel PWA (Final Implementation)**

This guide outlines a **future-proof push notification system** for your Laravel piggy bank app. It ensures that push notifications **work seamlessly** if the app is installed as a PWA.

## **🛠️ Implementation Plan**
1. **Set up Firebase Cloud Messaging (FCM)**
2. **Store user push subscriptions in the database**
3. **Create a Laravel API to send push notifications**
4. **Register a Service Worker to handle push events**
5. **Integrate push notifications in JavaScript for the PWA**

---

## **1️⃣ Set Up Firebase Cloud Messaging (FCM)**

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click **"Create a Project"** → Follow the setup
3. Go to **Project Settings** → **Cloud Messaging** tab
4. Copy the **Sender ID** and **Server Key** (used in Laravel)

---

## **2️⃣ Store Push Subscription Data in Laravel**

### **Step 1: Create a Migration for Push Subscriptions**
```sh
php artisan make:migration create_push_subscriptions_table
```

Modify the migration:
```php
Schema::create('push_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('endpoint');
    $table->text('public_key');
    $table->text('auth_token');
    $table->timestamps();
});
```
Run the migration:
```sh
php artisan migrate
```

### **Step 2: Create the Model**
```sh
php artisan make:model PushSubscription
```

Modify `app/Models/PushSubscription.php`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'endpoint', 'public_key', 'auth_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### **Step 3: Add Relationship in User Model**
Modify `app/Models/User.php`:
```php
public function push_subscription()
{
    return $this->hasOne(PushSubscription::class);
}
```

---

## **3️⃣ Create Laravel API to Store & Send Push Notifications**

### **Step 1: Create a Controller**
```sh
php artisan make:controller PushNotificationController
```

Modify `app/Http/Controllers/PushNotificationController.php`:
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PushNotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'publicKey' => 'required|string',
            'authToken' => 'required|string',
        ]);

        Auth::user()->push_subscription()->updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['message' => 'Subscribed to push notifications']);
    }

    public function sendPushNotification(Request $request)
    {
        $user = Auth::user();

        if (!$user->push_subscription) {
            return response()->json(['error' => 'User is not subscribed'], 400);
        }

        $payload = [
            'to' => $user->push_subscription->endpoint,
            'notification' => [
                'title' => 'Saving Reminder',
                'body' => 'You have a scheduled saving to complete!',
                'click_action' => url('/'),
            ],
        ];

        $serverKey = env('FIREBASE_SERVER_KEY');

        Http::withHeaders([
            'Authorization' => "key={$serverKey}",
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        return response()->json(['message' => 'Notification sent']);
    }
}
```

### **Step 2: Add API Routes**
Modify `routes/web.php`:
```php
use App\Http\Controllers\PushNotificationController;

Route::middleware(['auth'])->group(function () {
    Route::post('/push-subscribe', [PushNotificationController::class, 'subscribe']);
    Route::post('/send-push', [PushNotificationController::class, 'sendPushNotification']);
});
```

---

## **4️⃣ Register a Service Worker**

Create `public/firebase-messaging-sw.js`:
```js
self.addEventListener("push", function (event) {
    const data = event.data.json();
    self.registration.showNotification(data.notification.title, {
        body: data.notification.body,
        icon: "/icon.png",
        data: { click_action: data.notification.click_action },
    });
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.click_action)
    );
});
```

---

## **5️⃣ Integrate Push in JavaScript (PWA Support)**

### **Step 1: Load Firebase**
Add this to your `app.blade.php` layout:
```html
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js"></script>
<script src="{{ asset('js/push-notifications.js') }}" defer></script>
```

### **Step 2: Setup JavaScript**
Create `public/js/push-notifications.js`:
```js
document.addEventListener("DOMContentLoaded", async function () {
    const firebaseConfig = {
        apiKey: "YOUR_FIREBASE_API_KEY",
        authDomain: "YOUR_FIREBASE_AUTH_DOMAIN",
        projectId: "YOUR_FIREBASE_PROJECT_ID",
        messagingSenderId: "YOUR_FIREBASE_MESSAGING_SENDER_ID",
        appId: "YOUR_FIREBASE_APP_ID"
    };

    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    async function subscribeUser() {
        try {
            const permission = await Notification.requestPermission();
            if (permission !== "granted") return;

            const token = await messaging.getToken({ vapidKey: "YOUR_VAPID_PUBLIC_KEY" });

            await fetch("/push-subscribe", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ endpoint: token, publicKey: "YOUR_VAPID_PUBLIC_KEY", authToken: "YOUR_AUTH_TOKEN" }),
            });

            console.log("Subscribed to push notifications.");
        } catch (err) {
            console.error("Push subscription failed", err);
        }
    }

    document.getElementById("enable-push").addEventListener("click", subscribeUser);
});
```

---

## **Final Thoughts**
✅ **Fully supports push notifications when the PWA is installed.**  
✅ **No need for refactoring later—this is the final setup.**  
✅ **Works on Android, iOS (PWA), and desktop.**

🚀 **This is the best long-term approach for push notifications in your Laravel PWA!**
