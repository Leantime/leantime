import jQuery from "jquery";
import 'js/libs/jquery.growl';

export default function (evt) {
    jQuery.get(leantime.appUrl+"/notifications/getLatestGrowl", function(data){
        console.log(data);
        let notification = JSON.parse(data);
        jQuery.growl({
            message: notification.notification, style: notification.notificationType
        });
    });
};
