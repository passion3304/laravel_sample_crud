{{-- Bootstrap Notifications using Prologue Alerts & PNotify JS --}}
<script type="text/javascript">

// This is intentionaly run after dom loads so this way we can avoid showing duplicate alerts
// when the user is beeing redirected by persistent table, that happens before this event triggers.

window.addEventListener('DOMContentLoaded', function() {

    Noty.overrideDefaults({
            layout   : 'topRight',
            theme    : 'backstrap',
            timeout  : 2500,
            closeWith: ['click', 'button'],
        });

    //get alerts from the alert bag
    var $backpack_alerts = JSON.parse('@json(\Alert::getMessages())');

    //the will be no simultaneous alerts, either we get them from the alert bag,
    //or we already have them in the localstorage.
    var $passedAlerts = localStorage.getItem('backpack_alerts');

    if($passedAlerts !== null) {
        $backpack_alerts = JSON.parse($passedAlerts);
    }

    for (var type in $backpack_alerts) {
        for(var message in $backpack_alerts[type]) {
            new Noty({
                    type: type,
                    text: $backpack_alerts[type][message]
                }).show();
        }
    }

    //clear the localstorage alerts
    localStorage.removeItem('backpack_alerts');
});

</script>
