@component('mail::message')

@lang('confirmable::message.required')

@component('mail::button', ['url' => $url])
    @lang('confirmable::message.submit')
@endcomponent

Thanks,<br>
{{ ucwords(config('app.name')) }}
@endcomponent
