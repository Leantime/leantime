<?php

use Leantime\Core\UI\Theme;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$step = $tpl->get("currentStep");
?>

<?php

if (isset($_GET['step']) && $_GET['step'] == "complete") {?>
    <script>
        jQuery.nmTop().close();
    </script>
<?php } ?>

<div style="max-width:400px;">

<?php if ($step == 1) { ?>

<?php } ?>

<?php if ($step == 2) { ?>
    <form class="onboardingModal step2" method="post" name="inviteForm" action="<?=BASE_URL ?>/help/firstLogin?step=2">
        <input type="hidden" name="step" value="2" />
        <div class="row">
            <div class="col-md-6">
                <h1><?=$tpl->__('headlines.invite_crew'); ?></h1>
                <p><?=$tpl->__('text.invite_team') ?></p>
                <br />
                <input type="email" name="email1" value="" placeholder="<?=$tpl->__('input.placeholder.email_invite');?>" style="width: 100%;"/><br />
                <input type="email" name="email2" value="" placeholder="<?=$tpl->__('input.placeholder.email_invite');?>" style="width: 100%;"/><br />
                <input type="email" name="email3" value="" placeholder="<?=$tpl->__('input.placeholder.email_invite');?>" style="width: 100%;"/><br />
                <br />
                <input type="submit" value="<?=$tpl->__('buttons.next') ?>"/>
                <input type="submit" class="btn btn-outline" value="<?=$tpl->__('links.skip_for_now') ?>"/>

            </div>
            <div class="col-md-6">
                <div class='svgContainer' style="width:300px; margin-top:60px;">
                    <?= file_get_contents(ROOT . "/dist/images/svg/undraw_children_re_c37f.svg"); ?>
                </div>
            </div>
        </div>
    </form>

<?php } ?>

<?php if ($step == 3) { ?>
    <div class="row" style="width:60vw; height:80vh;">

        <div class="col-md-12">
            <center>
            <h1>Next step is to schedule your 1:1 onboarding call with our team.</h1>
            <p>Leantime is not just another project management tool. It's a new way of planning your work.<br />

                I have a few ideas on how the Leantime approach can help you become more focused & productive.</p>

                <!-- Start of Meetings Embed Script -->
                <div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/marcel-folaron/leantime-onboarding-call?embed=true"></div>
                <!-- End of Meetings Embed Script -->
                <script type="text/javascript">
                    var MeetingsEmbedCode=function(t){function e(t){return t.querySelectorAll("iframe").length>0}t.elementContainsIFrame=e;function n(t,e){return t||e?"&parentPageUrl="+t+e:""}t.getParentPageUrl=n;function o(t){var e=null;if(document.cookie&&""!==document.cookie)for(var n=document.cookie.split(";"),o=0;o<n.length;o++){var r=n[o].trim(),a=t+"=";if(r.substring(0,t.length+1)===a){e=r.substring(t.length+1);break}}return e}function r(t){return t?"&parentHubspotUtk="+t:""}t.getParentUtkParam=r;function a(t){return t?"&"+t.substr(1):""}t.getParentQueryParams=a;function i(t,e,n){return(t?"&ab="+t:"")+(e?"&abStatus="+e:"")+(n?"&contentId="+n:"")}t.buildAbTestingParams=i;function s(){var t=(new Date).getTime();return"xxxxxxxxxxxx4xxxyxxxxxxxxxxxxxxx".replace(/[xy]/g,(function(e){var n=(t+16*Math.random())%16|0;t=Math.floor(t/16);return("x"===e?n:3&n|8).toString(16)}))}function u(){var t=window.crypto||window.msCrypto,e=new Uint16Array(8);t.getRandomValues(e);var n=function(t){for(var e=t.toString(16);e.length<4;)e="0"+e;return e};return n(e[0])+n(e[1])+n(e[2])+n(e[3])+n(e[4])+n(e[5])+n(e[6])+n(e[7])}function c(){var t=window.crypto||window.msCrypto;return void 0!==t&&void 0!==t.getRandomValues&&void 0!==window.Uint16Array?u():s()}function d(){var t=window.__hsUserToken||o("hubspotutk");if(!t){var e=c();t=e;window.__hsUserToken=e}return t}t.getOrSetHubspotUtk=d;var p=["https://local.hubspot.com","https://local-eu1.hubspot.com","https://local.hubspotqa.com","https://local-eu1.hubspotqa.com","https://app.hubspot.com","https://app-eu1.hubspot.com","https://app.hubspotqa.com","https://app-eu1.hubspotqa.com","https://meetings.hubspot.com","https://meetings-eu1.hubspot.com","https://meetings.hubspotqa.com","https://meetings-eu1.hubspotqa.com"];function h(t){return p.indexOf(t)>-1}t.isHubSpotOrigin=h;function g(t,e){var n=t[t.message?"message":"data"];(h(t.origin)||t.origin.indexOf(window.origin)>-1)&&n.height&&(e.style.height=n.height+"px")}t.resize=g;var m=window._hsp=window._hsp||[];function l(t,e){t&&"readyForConsentListener"===t.data&&m.push(["addPrivacyConsentListener",function(t){var n=t.allowed,o=t.categories,r=n||o.analytics;e.contentWindow.postMessage({type:"privacy-consent",shouldTrackAnalytics:r},"*")}])}t.addPrivacyConsentListener=l;function w(n){var o,r=document.querySelectorAll(n),a=[];for(o=0;o<r.length;o++){var i=r[o],s=i.dataset.src,u=i.dataset.title,c=document.createElement("iframe"),d=i.dataset.ab,p=i.dataset.abStatus,h=i.dataset.contentId,g=t.getOrSetHubspotUtk();i.height="100%";c.src=s+t.getParentUtkParam(g)+t.getParentPageUrl(window.location.origin,window.location.pathname)+t.getParentQueryParams(window.location.search)+t.buildAbTestingParams(d,p,h);u&&(c.title=u);c.width="100%";c.style.minWidth="312px";c.style.minHeight="516px";c.style.height="756px";c.style.border="none";c.setAttribute("data-hs-ignore","true");if(!e(i)){i.appendChild(c);window.addEventListener("message",(function(e){t.resize(e,c)}));window.addEventListener("message",(function(e){t.addPrivacyConsentListener(e,c)}));a.push(c)}}return a}t.createMeetingsIframe=w;w(".meetings-iframe-container");window.hbspt||(window.hbspt={});window.hbspt.meetings||(window.hbspt.meetings={});window.hbspt.meetings.create=w;"object"==typeof exports&&(module.exports=t);return t}(MeetingsEmbedCode||{});
                </script>

            </center>

        </div>

    </div>
    <div class="clearall"></div>
    <div class="row">
        <div class="col-md-6">
            <br />
            <div id="redirectMessage" style="color:var(--accent2);"></div>
        </div>
        <div class="col-md-6 align-right">
            <br />
            <a href="javascript:void(0);" onclick="jQuery.nmTop().close();" class="btn btn-primary"><?=$tpl->__('buttons.lets_go') ?></a>
        </div>
    </div>
<?php } ?>

</div>
<script>
    jQuery(document).ready(function(){
        jQuery("#theme").on("change", function(){
            var themeName = jQuery("#theme option:selected").val();
            var url = "<?php echo BASE_URL; ?>/theme/"+themeName+"/css/theme.css";
            jQuery("#themeStylesheet").attr("href", url);
        });



    });

    function skipOnboarding() {

        jQuery("form.step1 #projectName").val('<?=session("currentProjectName") ?? '' ?>');
        jQuery("form.step1").submit();
        jQuery.nmTop().close();

    }
</script>


