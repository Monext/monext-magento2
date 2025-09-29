<?php

namespace Monext\Payline\Helper;

class Constants
{
    const CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG = 'payline_merchant_contract_import_flag';

    const WEB_PAYMENT_CPT = 'payline_web_payment_cpt';
    const WEB_PAYMENT_NX = 'payline_web_payment_nx';
    const WEB_PAYMENT_REC = 'payline_web_payment_rec';

    const AVAILABLE_WEB_PAYMENT_PAYLINE = [self::WEB_PAYMENT_CPT, self::WEB_PAYMENT_NX, self::WEB_PAYMENT_REC];

    CONST PAYLINE_RETURN_CART_EMPTY     = 0;
    CONST PAYLINE_RETURN_CART_FULL      = 1;
    CONST PAYLINE_RETURN_HISTORY_ORDERS = 2;


    CONST TOKEN_USAGE_ONCE         = 1;
    CONST TOKEN_USAGE_ONCE_HISTORY = 2;
    CONST TOKEN_USAGE_RECYCLE      = 3;


    const CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT = 'payline/general/environment';
    const CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_ID = 'payline/general/merchant_id';
    const CONFIG_PATH_PAYLINE_GENERAL_SOFT_DESCRIPTOR = 'payline/general/soft_descriptor';
    const CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_NAME = 'payline/general/merchant_name';
    const CONFIG_PATH_PAYLINE_GENERAL_SMARTDISPLAY_PARAM = 'payline/general/smartdisplay_parameter';
    const CONFIG_PATH_PAYLINE_GENERAL_ACCESS_KEY = 'payline/general/access_key';
    const CONFIG_PATH_PAYLINE_GENERAL_LANGUAGE = 'payline/general/language';
    const CONFIG_PATH_PAYLINE_GENERAL_DEBUG = 'payline/general/debug';
    const CONFIG_PATH_PAYLINE_GENERAL_DISABLE_FAILOVER = 'payline/general/disable_failover';
    const CONFIG_PATH_PAYLINE_GENERAL_TOKEN_USAGE = 'payline/general/token_usage';
    const CONFIG_PATH_PAYLINE_GENERAL_CONTRACTS = 'payline/general/contracts';

    const CONFIG_PATH_PAYLINE_DELIVERY = 'payline/payline_common/address';
    const CONFIG_PATH_PAYLINE_PREFIX = 'payline/payline_common/prefix';
    const CONFIG_PATH_PAYLINE_RETURN_REFUSED = 'payline/payline_common/return_payment_refused';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYTIME = 'payline/common_default/deliverytime';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYMODE = 'payline/common_default/deliverymode';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERY_EXPECTED_DELAY = 'payline/common_default/delivery_expected_delay';
    const CONFIG_PATH_PAYLINE_DEFAULT_PREFIX = 'payline/common_default/prefix';
    const CONFIG_PATH_PAYLINE_DEFAULT_CATEGORY = 'payline/common_default/category';


    const CONFIG_PATH_PAYLINE_ERROR_TYPE   = 'payline/general/user_error_message_';
    const CONFIG_PATH_PAYLINE_ERROR_DEFAULT = 'payline/general/user_error_message_default';

    //Const NX
    const CONFIG_PATH_PAYLINE_NX_MINIMUM_AMOUNT = 'payment/'.self::WEB_PAYMENT_NX.'/active_amount_min';

    //Const REC
    const CONFIG_PATH_PAYLINE_REC_ALLOWED_TYPE = 'payment/'.self::WEB_PAYMENT_REC.'/allowed_type';
    const CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_ID = 'product_id';
    const CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_SKU = 'product_sku';
    const CONFIG_PAYLINE_REC_ALLOWED_PRODUCT_TYPE = 'product_type';
    const CONFIG_PATH_PAYLINE_REC_START_CYCLE = 'payment/'.self::WEB_PAYMENT_REC.'/start_cycle';
    const CONFIG_PATH_PAYLINE_REC_BILLING_DAY = 'payment/'.self::WEB_PAYMENT_REC.'/billing_day';
    const CONFIG_PATH_PAYLINE_REC_BILLING_NUMBER = 'payment/'.self::WEB_PAYMENT_REC.'/billing_number';
    const CONFIG_PATH_PAYLINE_REC_BILLING_CYCLE = 'payment/'.self::WEB_PAYMENT_REC.'/billing_cycle';
    const CONFIG_PATH_PAYLINE_REC_STATUS_CARD_SCHEDULE_EXPIRED = 'payment/'.self::WEB_PAYMENT_REC.'/status_when_credit_card_schedule_is_expired';
    const CONFIG_PATH_PAYLINE_REC_STATUS_SCHEDULE_ALERT = 'payment/'.self::WEB_PAYMENT_REC.'/status_when_payline_schedule_alert';
    const CONFIG_PATH_PAYLINE_REC_SEND_WALLET_ID = 'payment/'.self::WEB_PAYMENT_REC.'/send_wallet_id';
    const CONFIG_PATH_PAYLINE_REC_AUTOMATE_INVOICE_CREATION = 'payment/'.self::WEB_PAYMENT_REC.'/automate_invoice_creation';

    const CONFIG_PATH_PAYLINE_CPT_CAPTURE_ON_TRIGGER = 'payment/'.self::WEB_PAYMENT_CPT.'/capture_payment_triggered_on';
    const PAYLINE_CPT_CAPTURE_ON_SHIPMENT = 'shipment';
    const PAYLINE_CPT_CAPTURE_ON_INVOICE = 'invoice';

    //Raw path for system backend model
    const CONFIG_PATH_RAW_PAYLINE_GENERAL_CONTRACTS = 'groups/payline/groups/payline_contracts/fields/contracts/value';
    const CONFIG_PATH_RAW_PAYLINE_CPT_ACTION        = 'groups/payline/groups/payline_solutions/groups/payline_cpt/fields/payment_action/value';


    const ORDER_STATUS_PAYLINE_PENDING = 'payline_pending';
    const ORDER_STATUS_PAYLINE_WAITING_CAPTURE = 'payline_waiting_capture';
    const ORDER_STATUS_PAYLINE_CAPTURED = 'payline_captured';
    const ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE = 'payline_cycle_payment_in_capture';
    const ORDER_STATUS_PAYLINE_CANCELED = 'payline_canceled';
    const ORDER_STATUS_PAYLINE_ABANDONED = 'payline_abandoned';
    const ORDER_STATUS_PAYLINE_REFUSED = 'payline_refused';
    const ORDER_STATUS_PAYLINE_FRAUD = 'payline_fraud';
    const ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE = 'payline_waiting_acceptance';

    //Config NX / REC
    const COST_TYPE_NO_CHARGES = 0;
    const COST_TYPE_FIXE = 1;
    const COST_TYPE_PERCENT = 2;

    const ORDER_STATUS_PAYLINE_PENDING_ONEY  = 'pending_oney';

    const PAYLINE_API_USED_BY_PREFIX = 'Magento';

    const MODULE_NAME = 'Monext_Payline';

    const PAYLINE_LOG_FILENAME = 'payline.log';

    // Widget customzation
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_ENABLED = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_custom_enabled';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_LABEL = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_cta_label';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BG_COLOR = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_bg_color';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BG_COLOR_HEXADECIMAL = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_bg_color_hexadecimal';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR_HOVER_DARKER = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_bg_color_hover_darker';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR_HOVER_LIGHTER = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_bg_color_hover_lighter';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_COLOR = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_color';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_FONT_SIZE = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_font_size';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_BORDER_RADIUS = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_cta_border_radius';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_WIDGET_BG_COLOR = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_css_widget_bg_color';
    const CONFIG_PATH_PAYLINE_WIDGET_CUSTOMIZATION_CTA_TEXT_UNDER = 'payment/'.self::WEB_PAYMENT_CPT.'/widget_cta_text_under';
}
