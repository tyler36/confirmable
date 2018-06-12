{{-- PARTIAL:  Form for User Verification --}}

<form method="post" action="{{ route('confirm.update') }}">
    @csrf()

    {{-- FORM:      Email --}}
    <div class="field form-email">
        <label for="email" class="label">@lang('user.email')</label>
        <div class="control has-icons-left">
            <span class="icon is-small">
                <i class="material-icons">mail_outline</i>
            </span>
            <input name="email" type="email"
                required="true"
                value="{{ old('email') }}"
                placeholder="@lang('user.ph_email')"
                aria-label="@lang('user.email')"
                class="{{ $errors->has('email') ? 'input is-danger' : 'input' }}"
            >
        </div>

        @if ($errors->has('email'))
            <span class="help is-danger">{{ $errors->first('email') }}</span>
        @endif
    </div>


    {{-- FORM:      Token --}}
    <div class="field form-token">
        <label for="token" class="label">@lang('confirmable::message.token')</label>
        <div class="control has-icons-left">
            <span class="icon is-small">
                <i class="material-icons">vpn_key</i>
            </span>
            <input name="token" type="text"
                required="true"
                value="{{ old('token') }}"
                placeholder="@lang('confirmable::message.ph_token')"
                aria-label="@lang('confirmable::message.token')"
                class="{{ $errors->has('token') ? 'input is-danger' : 'input' }}"
            >
        </div>

        @if ($errors->has('token'))
            <span class="help is-danger">{{ $errors->first('token') }}</span>
        @endif
    </div>

    {{-- FORM:      Acceptance --}}
    <div class="field form-acceptance">
        <label for="terms" class="label">@lang('confirmable::message.terms')</label>
        <input type="checkbox" name="terms">
        I have read and agree to the terms and conditions and privacy policy of this site.
</form>
