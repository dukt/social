<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140620_122653_social_transfer_token extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        if (!craft()->db->tableExists('social_users'))
        {
            Craft::log('Creating the social_users table.', LogLevel::Info, true);

            // Create the craft_social_users table
            craft()->db->createCommand()->createTable('social_users', array(
                'userId'    => array('column' => 'integer', 'required' => true),
                'provider'  => array('required' => true),
                'socialUid' => array('required' => true),
                'tokenId'   => array('maxLength' => 11, 'decimals' => 0, 'required' => false, 'unsigned' => false, 'length' => 10, 'column' => 'integer'),
            ), null, true);

            // Add indexes to craft_social_users
            craft()->db->createCommand()->createIndex('social_users', 'provider,socialUid', true);

            // Add foreign keys to craft_social_users
            craft()->db->createCommand()->addForeignKey('social_users', 'userId', 'users', 'id', 'CASCADE', null);
        }

        $this->transferUserTokens();


		return true;
	}

    private function transferUserTokens()
    {
        try {

            if(file_exists(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php'))
            {
                require_once(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php');
            }

            if(class_exists('OAuth\OAuth2\Token\StdOAuth2Token'))
            {
                // get token record

                $rows = craft()->db->createCommand()
                    ->select('*')
                    ->from('oauth_old_tokens')
                    ->where('userId IS NOT NULL')
                    ->queryAll();

                if($rows)
                {
                    foreach($rows as $row)
                    {
                        // transform token

                        $token = @unserialize(base64_decode($row['token']));

                        if($token)
                        {
                            if(get_class($token) == 'OAuth\OAuth1\Token\Access')
                            {
                                // oauth 1
                                $newToken = new \OAuth\OAuth1\Token\StdOAuth1Token();
                                $newToken->setAccessToken($token->access_token);
                                $newToken->setRequestToken($token->access_token);
                                $newToken->setRequestTokenSecret($token->secret);
                                $newToken->setAccessTokenSecret($token->secret);
                            }
                            else
                            {
                                // oauth 2
                                $newToken = new \OAuth\OAuth2\Token\StdOAuth2Token();
                                $newToken->setAccessToken($token->access_token);
                                $newToken->setLifeTime($token->expires);

                                if (isset($token->refresh_token))
                                {
                                    $newToken->setRefreshToken($token->refresh_token);
                                }
                            }

                            if (isset($newToken) && is_object($newToken))
                            {
                                $user = craft()->users->getUserById($row['userId']);

                                if($user)
                                {
                                    // save token
                                    $model = new Oauth_TokenModel;
                                    $model->providerHandle = $row['provider'];
                                    $model->pluginHandle = 'social';
                                    $model->encodedToken = craft()->oauth->encodeToken($newToken);
                                    craft()->oauth->saveToken($model);

                                    // save social user
                                    $socialUser = new Social_UserModel;
                                    $socialUser->userId = $row['userId'];
                                    $socialUser->provider = $row['provider'];
                                    $socialUser->socialUid = $row['userMapping'];
                                    $socialUser->tokenId = $model->id;

                                    if(!craft()->social->saveUser($socialUser))
                                    {
                                        Craft::log('Couldn’t save user.', LogLevel::Info, true);
                                    }
                                    else
                                    {
                                        Craft::log('User saved.', LogLevel::Info, true);
                                    }
                                }
                            }
                        }
                        else
                        {
                            Craft::log('Token error.', LogLevel::Info, true);
                        }
                    }
                }
                else
                {
                    Craft::log('Token record error.', LogLevel::Info, true);
                }
            }
            else
            {
                Craft::log('Class error.', LogLevel::Info, true);
            }
        }
        catch(\Exception $e)
        {
            Craft::log($e->getMessage(), LogLevel::Info, true);
        }
    }
}
