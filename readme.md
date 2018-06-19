# Introduction
This package is designed to simplify confirming a model (eg. User).
- Users generate a confirmation model and are emailed the token.
- To confirm, a user must enter their email and the confirmation token sent with a set time limit
- Users can request a new confirmation token.

## Installation
- Install package
```
composer require tyler36/confirmable-trait
```

- Publish the assets via command line
```
php artisan vendor:publish --provider=Tyler36\ConfirmableTrait\ConfirmableServiceProvider
```

- Add trait to User model
In your User model, add the following line
```
// App\User.php
use Tyler36\ConfirmableTrait\Confirmable;

Class User extends Model
...
    use Confirmable;
```

- Run the migrations
Update the User table by running the published migrations
```
php artisan migrate
```

- Register the event
```
// App\Providers\EventServiceProvider.php
...
    protected $listen = [
        UserRequestedConfirmationEmail::class => [
            SendConfirmationEmail::class
        ]
    ];
```

- Update views
Update the view in ```ConfirmUserController.php``` to point to the view page
```
// App\Http\Controllers\Auth\ConfirmUserController.php
...
    public function edit()
    {
        return view('auth.confirmation');
    }
```
You can either redirect all authenticated, unconfirmed users view applying the middleware or adding a simple link to the view.
I like to add a notification to the profile page.
```
// view/user/show.blade.php
...
    @if($user->isNotConfirmed())
        @include('common.confirmation_required')
    @endif
```
```
// view/forms/confirmation.blade.php
...
    <
```

- Apply middleware
This package comes with 2 middleware for protecting route.

### isConfirmed
This middleware only allows confirmed members. IE. A member is currently logged in AND marked as confirmed.
To register the middleware, update ```App\Http\Kernel.php``` as followed:
```
// App\Http\Kernel.php
...
protected $routeMiddleware = [
    ...
    'auth.confirmed'    => \Tyler36\ConfirmableTrait\Middleware\isConfirmed::class,
]
```
Of-course, you can change the middleware name ('auth.confirmed') to anything you.

### isNotConfirmed
This middleware only allows unconfirmed members. IE. A member is currently logged in AND is NOT confirmed.
To register the middleware, update ```App\Http\Kernel.php``` as followed:
```
// App\Http\Kernel.php
...
protected $routeMiddleware = [
    ...
    'auth.notconfirmed'    => \Tyler36\ConfirmableTrait\Middleware\isNotConfirmed::class,
]
```



## Models
### User model
After adding this trait to the User model, several new functions are available:

- To check if the User has been confirmed (returns boolean).
```
$user->isConfirmed()
```
- To check if User is NOT confirmed (returns boolean).
```
$user->isNotConfirmed()
```
- To manual mark a user as confirmed
```
$user->markConfirmed()
```
- You can get the current confirmation model via a relationship
```
$user->confirmation
```

### Confirmation model
This model holds a confirmation token and the email account associated with it.
- You can get the user via a relationship
```
$confirmation->user
```

#### Validating tokens
This packaged is designed to confirm users by checking a record matching an email & token exists.
There are several layers to validation
- Check a confirmation token exists for the authenticated User
```
$confirmation = Confirmation::firstOrFail(['email' => auth()->user()->email]);
```

- Validate a user supplied $token within the time limit
```
$confirmation->validateToken($token)
```
You can override the default 24 hour time period by updating the confirmation model
```
// Confirmation.php
class Confirmation extends Model
{
    ...
    protected $validForHours = 24;
```


## Factory Helpers
2 additional factory state helpers are available for your User models

### 'isConfirmed' User state
This will generate a user that has been confirmed. IE. 'confirmed' => true
```
factory(App\User::class)->states('isConfirmed')->create();
```

### 'isNotConfirmed' User state
This will generate a user that is NOT confirmed. IE. 'confirmed' => false
```
factory(App\User::class)->states('isNotConfirmed')->create();
```

## Translations
After publishing the assets, you can override package translations through the vendor translation files.
Note the double colon after the package name.
```
// In PHP files
trans('confirmable::message.token.mismatch')

// In blade files
@lang('confirmable::message.token.mismatch')
```
