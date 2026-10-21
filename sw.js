// Service Worker එක පසුබිමෙන් ක්‍රියාත්මක වෙමින් Popup Notification එක පෙන්වීම
self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'SHOW_NOTIFICATION') {
        const options = {
            body: event.data.body,
            icon: 'uploads/logo.png', // ඔබේ හෝටල් ලෝගෝ එකක් ඇත්නම් (Optional)
            vibrate: [200, 100, 200],
            data: { url: 'messages.php' },
            tag: 'new-message-' + Date.now() // එක පිට එක පණිවිඩ ඒම වැළැක්වීමට
        };

        self.registration.showNotification(event.data.title, options);
    }
});

// Notification එක උඩ ක්ලික් කළ විට messages.php පිටුවට රැගෙන යාම
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // දැනටමත් සයිට් එක ඇරලා තියෙනවා නම් ඒ Tab එකට යන්න, නැත්නම් අලුතින් ඇරලා යන්න
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url.includes('messages.php') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow('messages.php');
            }
        })
    );
});