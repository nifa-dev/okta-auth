<?php

use Cake\Core\Configure;
use Cake\View\Helper\UrlHelper;

echo $this->Html->script("https://ok1static.oktacdn.com/assets/js/sdk/okta-signin-widget/2.1.0/js/okta-sign-in.min.js");
echo $this->Html->css("https://ok1static.oktacdn.com/assets/js/sdk/okta-signin-widget/2.1.0/css/okta-sign-in.min.css");
echo $this->Html->css("https://ok1static.oktacdn.com/assets/js/sdk/okta-signin-widget/2.1.0/css/okta-theme.css");

?>


<body>
<div id="okta-login-container"></div>

</body>

<script type="text/javascript">
    const signIn = new OktaSignIn({
        baseUrl: 'https://<?= Configure::read('Credentials.domain') ?>',
        clientId: '<?= Configure::read('Credentials.clientId') ?>',
        redirectUri: '<?= Configure::read('OktaConfig.redirectUrl') ?>',
        authParams: {
            issuer: 'https://<?= Configure::read('Credentials.domain') ?>/oauth2/default',
            responseType: 'code',
            scopes: ['openid', 'email', 'profile']
        },
        i18n: {
            en: {
                'primaryauth.title': 'Please log in below:'
            }
        }
    });

    signIn.renderEl({ el: '#okta-login-container' }, () => {});

</script>

