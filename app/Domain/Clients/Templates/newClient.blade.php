@extends($layout)
@section('content')

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1>{!! __('headline.new_client') !!}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="widget">
           <h4 class="widgettitle">{!! __('subtitle.details') !!}</h4>
           <div class="widgetcontent">

                <form action="" method="post" class="stdform">

                    @dispatchEvent('afterFormOpen')

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.name') !!}</label>
                                <div class="span6">
                                    <input type="text" name="name" id="name" value="{{ $values['name'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.email') !!}</label>
                                <div class="span6">
                                    <input type="text" name="email" id="email" value="{{ $values['email'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.url') !!}</label>
                                <div class="span6">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="{{ $values['internet'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.street') !!}</label>
                                <div class="span6">
                                    <input
                                            type="text" name="street" id="street"
                                            value="{{ $values['street'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.zip') !!}</label>
                                <div class="span6">
                                    <input type="text"
                                           name="zip" id="zip" value="{{ $values['zip'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.city') !!}</label>
                                <div class="span6">
                                    <input type="text"
                                           name="city" id="city" value="{{ $values['city'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.state') !!}</label>
                                <div class="span6">
                                    <input
                                            type="text" name="state" id="state"
                                            value="{{ $values['state'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.country') !!}</label>
                                <div class="span6">
                                    <input
                                            type="text" name="country" id="country"
                                            value="{{ $values['country'] }}" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label">{!! __('label.phone') !!}</label>
                                <div class="span6">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="{{ $values['phone'] }}" />
                                </div>
                            </div>

                            @dispatchEvent('beforeSubmitButton')

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <input type="submit" name="save" id="save"
                                           value="{{ __('buttons.save') }}" class="btn btn-primary" />
                                </div>
                                <div class="span6">

                                </div>
                            </div>
                        </div>
                    </div>

                    @dispatchEvent('beforeFormClose')

                </form>
            </div>
        </div>
    </div>
</div>

@endsection
