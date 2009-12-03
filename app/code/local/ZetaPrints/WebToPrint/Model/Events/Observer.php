<?php

class ZetaPrints_WebToPrint_Model_Events_Observer {

  public function create_zetaprints_order ($observer) {
    $option_model = $observer->getEvent()->getQuoteItem()->getOptionByCode('info_buyRequest');
    $options = unserialize($option_model->getValue());

    if (!(isset($options['zetaprints-TemplateID']) || isset($options['zetaprints-previews'])))
      return;

    if (!(isset($options['zetaprints-TemplateID']) && isset($options['zetaprints-previews'])))
      Mage::throwException('Not enough ZetaPrints template parameters');

    $params = array();

    $params['TemplateID'] = $options['zetaprints-TemplateID'];
    $params['Previews'] = $options['zetaprints-previews'];

    $w2p_user = Mage::getModel('api/w2puser');

    //$params['ApiKey'] = $w2p_user->key;

    $user_credentials = $w2p_user->get_credentials();
    $params['ID'] = $user_credentials['id'];
    $params['Hash'] = zetaprints_generate_user_password_hash($user_credentials['password']);

    $order_id = zetaprints_get_order_id (Mage::getStoreConfig('api/settings/w2p_url'), $w2p_user->key, $params);

    if (!preg_match('/^[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}$/', $order_id))
      Mage::throwException('ZetaPrints error');

    $options['zetaprints-order-id'] = $order_id;
    $option_model->setValue(serialize($options));
  }
}

?>