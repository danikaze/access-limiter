# AccessLimiter

Very small script to allow accessing specified files a limited number of times.

It's just based on calling a function that will use a lock file with the number of times that it's has been checked.
Then, the method `isAllowed` will return `true` or `false` and proper action can be taken.

It's recommended to have a folder just for locks (depending on how many files you wanna lock).
Lock files can be shared, but at this point, the check will be shared too (i.e. two files with the same lock file and `maxViews` set to 2, will be _not allowed_ after one file is accessed twice, or each file once).

Also, it's recommend to hide your logs and private files with server side rules (i.e. apache .htaccess) so they cannot be accessed externaly bypassing the common url.

## Documentation ##

Options accessed when creating an `AccessLimiter` instance are:

* **file**: (_Default `null`_) Just for logging. If not specified it will use the `lockFile` to output the log.
* **lockFile**: (_Default `$_SERVER['SCRIPT_FILENAME'] . '.lock'`_) Where is the file used for checking the number of views. No database is needed.
* **logFile**: (_Default `dirname($_SERVER['SCRIPT_FILENAME']) . '/log.txt'`_) Where to write the log when it's enabled.
* **logEnabled**: (_Default `true`) Set to `false` to disable logging.
* **logFormat**: (_Default `'%TIME% %IP% > %FILE% %VIEWS% [%CASE%] %CLIENT%'`_) Format for each log entry. Available placeholders are:
  `%TIME%`: Time of the access. It's logged in UTC.
  `%IP%`: IP of the accesser.
  `%CASE%`: _Blocked_, _Shown_ or _Bypass_
  `%VIEWS%`: Number of access to the file. (Bypassed accesses don't increment this number. Blocked accesses do, but it doesn't mean that the file is shown)
  `%FILE%`: Value specified in `file`, or `lockFile` if nothing.
  `%CLIENT%`: User Agent of the accesser.
* **maxViews**: (_Default `1`_) Once this number is reached, `isAllowed` will start returning `false` and the case will be _Blocked_ unless it's bypassed.
* **byPassKey**: (_Default `null`_) For admin purposes, if set and this value is found as a GET parameter, the `isAllowed` will return `true` and the case will be _Bypass_

**Example:**

```php
$limiter = new AccessLimiter(array(
  'maxViews' => 3,
  'file'     => 'img.jpg',
  'lockFile' => __DIR__ . '/locks/img.jpg.lock',
  'logFile'  => __DIR__ . '/log/log.txt',
));
```

### Public methods

#### isAllowed()

Checks if the file is allowed to be shown or should be blocked. It also logs an entry if the log is enabled.

**Example:**

```php
if ($limiter->isAllowed()) {
  echo "Allowed";
} else {
  echo "Blocked";
  exit;
}
```

Even if `isAllowed` is called multiple times, the log entry is created only the first time for the same instance of the `AccessLimiter` object.


#### getCase()

Returns an String with the case which can be `CASE_ALLOW`, `CASE_BLOCK` or `CASE_BYPASS`.
It will return `null` if called before calling `isAllowed`.

**Example:**

```php
$isAdmin = $limiter->getCase() === AccessLimiter::CASE_BYPASS;
```

#### sendMail($to, $options)

Send an email to the specified address (`$to`) inform about the access.
The options are:
* **timezone**: (_Default `'GMT'`_) Since the time is logged in GMT, you can specify an string as accepted by `DateTimeZone` with the desired timezone to receive the time in.
* **timeformat**: (_Default `'c'`_) As used by `date`.
* **logUrl**: (_Default `null`_) If specified, it will be added in the email for easier access to the logs. This url is recommended to be private and protected with some kind of logging system ;)

**Example:**

```php
$limiter->sendMail('watcher@mail.com', array(
  'timezone'   => 'Asia/Tokyo',
  'timeformat' => 'Y-m-d H:i:s',
  'logUrl'     => 'http://url.com/log/log.txt',
));
```

#### reset($options)

Reset the instance. It's the same that creating a new one, but without having to create it.
The accepted options are the same that the ones in the constructor.

Since the instance is reseted, the next time `isAllowed` is called, it will generate a log entry.

## Examples

Check:
* **.htaccess** in the root to see how to provide controlled access to private files.
* **test.php** to see how to use this with generic documents (as html files).
* **img.php** to see how to provide access to binary files.