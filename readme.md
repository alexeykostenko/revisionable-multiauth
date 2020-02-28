## Multiauth for [omaximus/revisionable](https://github.com/omaximus/revisionable)
Added multiauth for omaximus/revisionable.

## Telescope Error Service Client

#### Installation

You need to add section repositories in your composer.json, for example:

```
"repositories": {
    "revisionable-multiauth": {
      "type": "vcs",
      "url": "git@github.com:alexeykostenko/revisionable-multiauth.git"
    }
}
```

Require package:
```
composer require alexeykostenko/revisionable-multiauth:dev-master
```

Finally, you'll also need to run migration on the package
```
php artisan migrate
```

#### Implementation

For any model that you want to keep a revision history for, include the `Pdffiller\RevisionableMultiauth` namespace and use the `RevisionableTrait` in your model, for example:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Pdffiller\RevisionableMultiauth\RevisionableTrait;

class Article extends Model
{
    use RevisionableTrait;
}
```

You can find additional docs on the page https://github.com/VentureCraft/revisionable/blob/master/readme.md
