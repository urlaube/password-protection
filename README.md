# Password-Protection plugin
The Password-Protection plugin is a plugin for [Urlaub.be](https://github.com/urlaube/urlaube) that allows you to protect individual content files with a password.

## Installation
Place the folder containing the plugin into your plugins directory located at `./user/plugins/`.

## Configuration
At the moment this plugin has no configuration.

## Usage
To protect a content file with a password you have to add a `Password:` header to the content file. This header can contain one or more passwords that are separated by a whitespace character. You can either provide plaintext passwords or hashed passwords as supported by the [`password_verify`](https://www.php.net/manual/en/function.password-verify.php) function of PHP.

A hashed password can e.g. be prepared like this:

```
$ php -r "print(PHP_EOL.password_hash(readline('Password: '), PASSWORD_BCRYPT, ['cost' => 12]).PHP_EOL);"
```

By default the content is replaced by a password submission form. The content will only be displayed when a correct password is provided through the password submission form.
