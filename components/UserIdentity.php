<?php

namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\models\User;
use webvimark\modules\UserManagement\UserManagementModule;
use yii\base\Security;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\ServerErrorHttpException;
use Yii;

/**
 * Parent class for User model.
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $confirmation_token
 * @property int $status
 * @property int $superadmin
 * @property int $created_at
 * @property int $updated_at
 */
abstract class UserIdentity extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => User::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => User::STATUS_ACTIVE]);
    }

    /**
     * Finds user by confirmation token.
     *
     * @param string $token confirmation token
     *
     * @return static|null|User
     */
    public static function findByConfirmationToken($token)
    {
        $expire = Yii::$app->getModule('user-management')->confirmationTokenExpire;

        $parts = explode('_', $token);
        $timestamp = (int) end($parts);

        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'confirmation_token' => $token,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by confirmation token.
     *
     * @param string $token confirmation token
     *
     * @return static|null|User
     */
    public static function findInactiveByConfirmationToken($token)
    {
        $expire = Yii::$app->getModule('user-management')->confirmationTokenExpire;

        $parts = explode('_', $token);
        $timestamp = (int) end($parts);

        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'confirmation_token' => $token,
            'status' => User::STATUS_INACTIVE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password, locally or via LDAP.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        // Local user
        if ($this->auth_type === 'local') {
            return Yii::$app->security->validatePassword($password, $this->password_hash);
        }

        // LDAP user
        if ($this->auth_type === 'ldap') {
            $user = $this->username;
            $ldap_host = Yii::$app->params['ldap']['host'];
            $ldap_port = Yii::$app->params['ldap']['port'];
            $base_dn = Yii::$app->params['ldap']['base_dn']; // Active Directory base DN
            $dn = "uid=$user, $base_dn"; // Distinguised Name

            // Connecting to LDAP server
            // The "@" will silence any php errors and warnings the function could raise.
            // See http://php.net/manual/en/language.operators.errorcontrol.php
            if (!$ds = @ldap_connect($ldap_host, $ldap_port)) {
                throw new ServerErrorHttpException(UserManagementModule::t('back', 'The provided LDAP parameters are syntactically wrong.'));
            }
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

            // ldap_connect() does not actually test the connection to the
            // specified LDAP server, it is just a syntactic check.
            // The only way to test the connection is to actually call
            //   ldap_bind($ds, $username, $password).
            // But if that fails, is it because credentials were wrong,
            // or is it because the app could not reach the LDAP server?
            //
            // One possible workaround is to try an anonymous bind first.
            // Note that this workaround relies on anonymous login being enabled.
            // TODO: Explore ldap_error($ds) and LDAP_OPT_DIAGNOSTIC_MESSAGE.
            if (!($anon = @ldap_bind($ds))) {
                throw new ServerErrorHttpException(UserManagementModule::t('back', 'Could not bind to the LDAP server.'));
            } else {
                // Test passed.  Unbind anonymous.
                ldap_unbind($ds);
            }

            // Reconnect and try a real login.
            if (!$ldapconn = @ldap_connect($ldap_host, $ldap_port)) {
                throw new ServerErrorHttpException(UserManagementModule::t('back', 'The provided LDAP parameters are syntactically wrong.'));
            }
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search on Windows 2003 Server Active Directory.
            if (!($bind = @ldap_bind($ldapconn, $dn, $password))) {
                // Authentication failed
                ldap_unbind($ldapconn);

                return false;
            }

            // Successfully authenticated!
            // If it is the first the user logs in, let's add it to the database.
            if (!$this->id) {
                $this->getUserAttributes($ldapconn, $base_dn);
                $this->save();
            }

            ldap_unbind($ldapconn);

            return true;
        }

        throw new ServerErrorHttpException(UserManagementModule::t('back', 'Unknown auth type.'));
    }

    /**
     * Search user attributes in the LDAP server, and add them to the User object.
     *
     * You may want to override this function in your custom User class.
     * This is just a placeholder and example.
     *
     * @param resource $ldapconn An LDAP link identifier, returned by ldap_connect()
     * @param string   $base_dn  The base DN for the directory
     */
    protected function getUserAttributes($ldapconn, $base_dn)
    {
        /*
        $filter = "(uid=$this->username)";
        // RFC specifications define many standard LDAP attributes, including
        // RFC 2256: cn (Common Name)
        // RFC 2798: mail (primary e-mail address)
        // RFC 2307: uidNumber (user's integer identification number)
        $attributes = ['mail'];
        $results = @ldap_search($ldapconn, $base_dn, $filter, $attributes);
        if (!$results) {
            throw new ServerErrorHttpException(Yii::t('app', 'Unable to search LDAP server'));
        }
        // $number_returned = ldap_count_entries($ldapconn, $results);
        $entries = ldap_get_entries($ldapconn, $results);
        $mail = $entries[0]['mail'][0];

        $this->email = $mail;
        */
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        if (php_sapi_name() == 'cli') {
            $security = new Security();
            $this->password_hash = $security->generatePasswordHash($password);
        } else {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        if (php_sapi_name() == 'cli') {
            $security = new Security();
            $this->auth_key = $security->generateRandomString();
        } else {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }
    }

    /**
     * Generates new confirmation token.
     */
    public function generateConfirmationToken()
    {
        $this->confirmation_token = Yii::$app->security->generateRandomString().'_'.time();
    }

    /**
     * Removes confirmation token.
     */
    public function removeConfirmationToken()
    {
        $this->confirmation_token = null;
    }
}
