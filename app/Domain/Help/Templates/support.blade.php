<div class="padding-lg" style="width:1190px;">
    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg image="undraw_unexpected-friends_42mc.svg" maxWidth="auto"  maxheight="auto" height="250px" headline=""></x-global::undrawSvg>
        </div>
    </div>
    <div class="row onboarding">
        <div class="col-md-12" style="font-size:var(--font-size-l);">

            <div class="col-md-12" style="font-size:var(--font-size-l);">
                <center>
                    <h1 class="fancyLink">{{ __('support.headline_help_us_build') }}</h1>
                    <p>{{ __('support.intro_text_1') }}</p>
                    <br />
                    <p>{{ __('support.intro_text_2') }}</p>
                    <br /> <br />
                </center>

                <h1 class="fancyLink">{{ __('support.headline_why_wont_disappear') }}</h1>
                <div class="tw-flex tw-w-full tw-justify-evenly tw-gap-5">
                    <div class="tw-flex-1" style="border: 1px solid var(--main-border-color); padding:15px; border-radius:var(--box-radius);">
                        <strong style="margin-bottom:5px; display:block;">{{ __('support.years_label') }}</strong>
                        {{ __('support.years_desc') }}
                    </div>
                    <div class="tw-flex-1" style="border: 1px solid var(--main-border-color); padding:15px; border-radius:var(--box-radius);">
                        <strong style="margin-bottom:5px; display:block;">{{ __('support.funding_model_label') }}</strong>
                        {{ __('support.funding_model_desc') }}
                    </div>
                    <div class="tw-flex-1" style="border: 1px solid var(--main-border-color); padding:15px; border-radius:var(--box-radius);">
                        <strong style="margin-bottom:5px; display:block;">{{ __('support.license_label') }}</strong>
                        {{ __('support.license_desc') }}
                    </div>
                </div>

                <br /><br /><br />
                <h1 class="fancyLink">{{ __('support.headline_how_can_you_help') }}</h1>
                <div class="tw-flex tw-w-full tw-justify-evenly tw-gap-5">
                    <div class="tw-flex-1" style="background:var(--header-gradient); color:var(--main-titles-color); padding:15px; border-radius:var(--box-radius);">
                        <strong style="margin-bottom:5px;  display:block; color:var(--main-titles-color);">{{ __('support.direct_sponsorship_label') }}</strong>
                        {{ __('support.direct_sponsorship_desc') }}<br /><br />
                        <x-global::forms.button tag="a" link="https://github.com/sponsors/Leantime" contentRole="primary" target="_blank" style="background:var(--main-titles-color); color:var(--accent1);">{{ __('support.sponsor_button') }}</x-global::forms.button>
                    </div>
                    <div class="tw-flex-1" style="background:var(--header-gradient); color:var(--main-titles-color); padding:15px; border-radius:var(--box-radius);">
                        <strong style="margin-bottom:5px;  display:block; color:var(--main-titles-color);">{{ __('support.purchase_plugins_label') }}</strong>
                        {{ __('support.purchase_plugins_desc') }}<br /><br />
                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/plugins/marketplace" contentRole="primary" style="background:var(--main-titles-color); color:var(--accent1);" target="_blank">{{ __('support.browse_marketplace_button') }}</x-global::forms.button>
                    </div>
                </div>

                <br /><br /><br />
                <h1 class="fancyLink">{{ __('support.headline_how_money_helps') }}</h1>
                <div class="tw-flex tw-w-full tw-justify-evenly tw-gap-5">
                    <div class="tw-flex-1" >
                        <div style="background:var(--dropdown-link-hover-bg); padding:15px; border-radius:var(--box-radius);">
                            <small>{{ __('support.funds_from_label') }}</small><br /><strong style="margin-bottom:5px; display:block;">{{ __('support.github_sponsorships_label') }}</strong>
                            <ul style="margin-left:15px;">
                                <li>{{ __('support.github_benefit_1') }}</li>
                                <li>{{ __('support.github_benefit_2') }}</li>
                                <li>{{ __('support.github_benefit_3') }}</li>
                                <li>{{ __('support.github_benefit_4') }}</li>
                            </ul>
                        </div>
                        <div style="padding:5px 10px;">
                            <strong><em>{{ __('support.recent_impact_label') }}<br/>{{ __('support.github_impact_text') }}</em></strong>
                        </div>
                    </div>
                    <div class="tw-flex-1" >
                        <div style="background:var(--dropdown-link-hover-bg); padding:15px; border-radius:var(--box-radius);">
                            <small>{{ __('support.funds_from_label') }}</small><br /> <strong style="margin-bottom:5px;  display:block;">{{ __('support.plugin_sales_label') }}</strong>
                            <ul style="margin-left:15px;">
                                <li>{{ __('support.plugin_benefit_1') }}</li>
                                <li>{{ __('support.plugin_benefit_2') }}</li>
                                <li>{{ __('support.plugin_benefit_3') }}</li>
                                <li>{{ __('support.plugin_benefit_4') }}</li>
                            </ul>
                        </div>
                        <div style="padding:5px 10px;">
                            <strong><em>{{ __('support.recent_impact_label') }}<br/>{{ __('support.plugin_impact_text') }}</em></strong>
                        </div>
                    </div>
                    <div class="tw-flex-1" >
                        <div style="background:var(--dropdown-link-hover-bg); padding:15px; border-radius:var(--box-radius);">
                            <small>{{ __('support.funds_from_label') }}</small><br /><strong style="margin-bottom:5px;  display:block;">{{ __('support.saas_revenue_label') }}</strong>
                            <ul style="margin-left:15px;">
                                <li>{{ __('support.saas_benefit_1') }}</li>
                                <li>{{ __('support.saas_benefit_2') }}</li>
                                <li>{{ __('support.saas_benefit_3') }}</li>
                                <li>{{ __('support.saas_benefit_4') }}</li>
                            </ul>
                        </div>
                        <div style="padding:5px 10px;">
                            <strong><em>{{ __('support.recent_impact_label') }}<br/>{{ __('support.saas_impact_text') }}</em></strong>
                        </div>
                    </div>
                </div>

                <br /><br /><br />
                <h1 class="fancyLink">{{ __('support.headline_impact_numbers') }}</h1>
                <div class="tw-flex tw-w-full tw-justify-center tw-gap-4">
                    <div class="tw-text-center tw-flex-1" style="background:#D6F3FF; padding:15px; border-radius:var(--box-radius); ">
                        <span style="color:var(--accent1); font-weight:bold; font-size:var(--font-size-xl);">50,000+</span>
                        <p>{{ __('support.installations_label') }}</p>
                    </div>
                    <div class="tw-text-center tw-flex-1" style="background:#EBF9FF; padding:15px; border-radius:var(--box-radius); ">
                        <span style="color:var(--accent1); font-weight:bold; font-size:var(--font-size-xl);">200+</span>
                        <p>{{ __('support.bugs_closed_label') }}</p>
                    </div>
                    <div class="tw-text-center tw-flex-1" style="background:#FEEBF3; padding:15px; border-radius:var(--box-radius); ">
                        <span style="color:var(--accent1); font-weight:bold; font-size:var(--font-size-xl);">40+</span>
                        <p>{{ __('support.languages_translated_label') }}</p>
                    </div>
                    <div class="tw-text-center tw-flex-1" style="background:#FBFDED; padding:15px; border-radius:var(--box-radius); ">
                        <span style="color:var(--accent1); font-weight:bold; font-size:var(--font-size-xl);">100%</span>
                        <p>{{ __('support.sponsorship_dev_label') }}</p>
                    </div>
                </div>

                <br /><br /><br />


                <h1 class="fancyLink">{{ __('support.headline_whos_behind') }}</h1>

                <div class="tw-flex tw-w-full tw-justify-evenly tw-gap-5">
                    <div class="tw-flex-1" style="background:var(--dropdown-link-hover-bg); padding:15px; border-radius:var(--box-radius);">
                        <img src="{{ BASE_URL }}/dist/images/marcel.png" style="float:right; width:100px; border:none; box-shadow:none; margin-left:10px; margin-bottom:10px;"/>
                        <p><strong style="margin-bottom:5px;  display:block;">{{ __('support.marcel_name') }}</strong>{{ __('support.marcel_tagline') }}</p>
                        <br />
                        <p>{{ __('support.marcel_bio_1') }}</p>

                        <p>{{ __('support.marcel_bio_2') }}</p><br />
                        <a href="https://www.linkedin.com/in/marcelfolaron/" target="_blank" ><i class="fa fa-linkedin"></i></a>
                    </div>

                    <div class="tw-flex-1" style="background:var(--dropdown-link-hover-bg); padding:15px; border-radius:var(--box-radius);">
                        <img src="{{ BASE_URL }}/dist/images/gloria.png" style="float:right; width:100px; border:none; box-shadow:none; margin-left:10px; margin-bottom:10px;"/>
                        <p><strong style="margin-bottom:5px;  display:block;">{{ __('support.gloria_name') }}</strong>{{ __('support.gloria_tagline') }}</p>
                        <br /><p>{{ __('support.gloria_bio_1') }}</p>
                        <p>{{ __('support.gloria_bio_2') }}</p><br />
                        <a href="https://www.linkedin.com/in/gloriafolaron/" target="_blank" ><i class="fa fa-linkedin"></i></a>
                    </div>
                </div>

                <br /><br />
                <div>
                    <center>
                        <p>{{ __('support.closing_text') }}</p><br /> <br />
                        <h1 class="fancyLink">{{ __('support.headline_ready_to_impact') }}</h1><p>{{ __('support.contribution_text') }}</p>
                        <br />
                        <div class="tw-text-center">
                            <x-global::forms.button tag="a" contentRole="primary" class="btn-lg" link="https://github.com/sponsors/Leantime" target="_blank" rel="noopener noreferrer">{{ __('support.start_sponsoring_button') }}</x-global::forms.button>
                        </div>
                    </center>
                </div>

                <br />
                <div class="clearall"></div>
            </div>
            <div class="clearall"></div>
        </div>
    </div>
</div>
