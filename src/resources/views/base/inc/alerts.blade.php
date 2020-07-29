{{-- Bootstrap Notifications using Prologue Alerts & PNotify JS --}}
<script type="text/javascript">

// This is intentionaly run after dom loads so this way we can avoid showing duplicate alerts
// when the user is beeing redirected by persistent table, that happens before this event triggers.

window.addEventListener('DOMContentLoaded', function() {
    //get the php alerts
    var $crudAlerts = JSON.parse('{!! json_encode(\Alert::getMessages()) !!}');

        Noty.overrideDefaults({
            layout   : 'topRight',
            theme    : 'backstrap',
            timeout  : 2500,
            closeWith: ['click', 'button'],
        });


    for (var type in $crudAlerts) {
        for(var message in $crudAlerts[type]) {
            new Noty({
                    type: type,
                    text: $crudAlerts[type][message]
                }).show();
        }
    }
    //if the user was redirected by persistent table we get any alerts to show from localstorage
    //as they are not in session anymore but we previously stored them.
    $passedAlerts = JSON.parse(localStorage.getItem('passAlerts'));

    if(typeof $passedAlerts === 'object' && $passedAlerts !== null) {
        $passedAlerts.forEach(function($item) {
                new Noty({
                    type: $item.type,
                    text: $item.text
                }).show();
        });
    }
    //clear the localstorage alerts
    localStorage.removeItem('passAlerts');
});

</script>
