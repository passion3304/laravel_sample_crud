{{-- Bootstrap Notifications using Prologue Alerts & PNotify JS --}}
<script type="text/javascript">
    // This is intentionaly run after dom loads so this way we can avoid showing duplicate alerts
    // when the user is beeing redirected by persistent table, that happens before this event triggers.
    window.addEventListener('DOMContentLoaded', function() {
        Noty.overrideDefaults({
            layout: 'topRight',
            theme: 'backstrap',
            timeout: 2500,
            closeWith: ['click', 'button'],
        });

        // get alerts from the alert bag
        var $alerts_from_php = JSON.parse('@json(\Alert::getMessages())');

        // get the alerts from the localstorage
        var $alerts_from_localstorage = JSON.parse(localStorage.getItem('backpack_alerts')) ?? {};

        // merge both php alerts and localstorage alerts
        Object.entries($alerts_from_php).forEach(([type, msg]) => {
            if(typeof $alerts_from_php[type] !== 'undefined') {
                $alerts_from_localstorage[type].push(msg);
            } else {
                $alerts_from_localstorage[type] = msg;
            }
        });

        console.log($alerts_from_localstorage);

        for (var type in $alerts_from_localstorage) {
            for(var message in $alerts_from_localstorage[type]) {
                new Noty({
                    type: type,
                    text: $alerts_from_localstorage[type][message]
                }).show();
            }
        }

        // in the end, remove backpack alerts from localStorage
        localStorage.removeItem('backpack_alerts');
    });
</script>
