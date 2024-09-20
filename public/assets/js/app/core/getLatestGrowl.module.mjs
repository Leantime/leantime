import jQuery from "jquery";
import { appUrl } from './instance-info.module.mjs';
import 'js/libs/jquery.growl';

export default function (evt) {
    jQuery.get(appUrl+"/notifications/getLatestGrowl", function(data){
        console.log(data);
        let notification = JSON.parse(data);
        jQuery.growl({
            message: notification.notification, style: notification.notificationType
        });
    });
};
