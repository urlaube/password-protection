<?php

  /**
    This is the Password-Protection plugin.

    This file contains the Password-Protection plugin. It provides a password
    protection feature that hides the content until a correct password is provided
    through a password submission form.

    @package urlaube\password-protection
    @version 0.1a0
    @author  Yahe <hello@yahe.sh>
    @since   0.1a0
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  class PasswordProtection extends BaseSingleton implements Plugin {

   // CONSTANTS

    const PASSWORD = "password";

    // HELPER FUNCTIONS

    protected static function checkPassword($content, $password) {
      // make sure we do not leak the content by error
      $result = null;

      // check if we have a post call
      if (POST === value(Main::class, METHOD)) {
        // check if the request comes from the website itself
        if (isset($_SERVER["HTTP_REFERER"])) {
          if (0 === strcmp(absoluteurl(value(Main::class, URI)), $_SERVER["HTTP_REFERER"])) {
            // check if a password was sent
            if (isset($_POST[self::PASSWORD])) {
              // convert password string to array by whitespaces
              $password = explode(SP, $password);

              // iterate through the array of passwords and compare them
              // with the submitted password, use password_verify() if the
              // password starts with a dollar sign
              $success = false;
              foreach ($password as $password_item) {
                if (0 === strpos($password_item, "\$")) {
                  $success = $success || password_verify($_POST[self::PASSWORD], $password_item);
                } else {
                  $success = $success || hash_equals($_POST[self::PASSWORD], $password_item);
                }
              }

              // on success we return the original content
              if ($success) {
                $result = $content;
              }
            }
          }
        }
      }

      // on a GET request or if the password is wrong
      // we display a password submission form instead,
      // if this is a POST request we assume that the
      // submitted password was wrong and we also show
      // an error message
      if (null === $result) {
        $iserror = (POST === value(Main::class, METHOD));

        // generate form source code
        $result = tfhtml("<link href=\"%s\" rel=\"stylesheet\">".NL.
                         "<form action=\"%s\" id=\"password-protection\" method=\"post\">".NL.
                         "  <p class=\"password-protection-description\">%s</p>".NL.
                         "  <p class=\"password-protection-password\">".NL.
                         "    <label for=\"password-protection-password\">%s*</label><br>".NL.
                         "    <input id=\"password-protection-password\" name=\"password\" required=\"required\" type=\"password\">".NL.
                         "  </p>".NL.
                         "  <div class=\"alert alert-danger\" id=\"%s\">%s</div>".NL.
                         "  <p class=\"password-protection-submit\">".NL.
                         "    <input id=\"password-protection-submit\" name=\"submit\" type=\"submit\" value=\"%s\">".NL.
                         "  </p>".NL.
                         "  <p class=\"password-protection-info\">%s</p>".NL.
                         "</form>",
                         static::class,
                         path2uri(__DIR__."/css/style.css"),
                         value(Main::class, URI),
                         "Dieser Inhalt ist passwortgeschützt.",
                         "Passwort",
                         $iserror ? "password-protection-failure-alert" : "password-protection-failure-hidden",
                         "Das eingegebene Passwort stimmt nicht überein!",
                         "Inhalt anzeigen",
                         "Pflichtfelder sind mit * markiert.");
      }

      return $result;
    }

    // RUNTIME FUNCTIONS

    public static function plugin($content) {
      $result = $content;

      if ($result instanceof Content) {
        if ($result->isset(CONTENT) && $result->isset(self::PASSWORD)) {
          $result->set(CONTENT, static::checkPassword(value($result, CONTENT), value($result, self::PASSWORD)));
        }
      } else {
        if (is_array($result)) {
          // iterate through all content items
          foreach ($result as $result_item) {
            if ($result_item instanceof Content) {
              if ($result_item->isset(CONTENT) && $result_item->isset(self::PASSWORD)) {
                $result_item->set(CONTENT, static::checkPassword(value($result_item, CONTENT), value($result_item, self::PASSWORD)));
              }
            }
          }
        }
      }

      return $result;
    }

  }

  // register plugin
  Plugins::register(PasswordProtection::class, "plugin", FILTER_CONTENT);

  // register translation
  Translate::register(__DIR__.DS."lang".DS, PasswordProtection::class);
