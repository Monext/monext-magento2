# 1.2.21
Released on 2025-02-04
- Add Floa payment compatibility
- Wallet: fix security and set updatePersonalDetails to 0
- Add display.rule.param in configuration to use in Smartdisplay
- Fix Patch\Data\UpdateCategoryMapping on database using a prefix

# 1.2.20
Released on 2024-11-14
- Add REC payment
- Add Klarna payment compatibility
- Update logos
- Update SDK dependency to fit rebranding monext/monext-php
- Fix cart restore on user payment cancelled
- Improve fr_FR translations
- Improve frontend redirect contracts template
- Add log visibility in backoffice

# 1.2.19
Released on 2024-05-23
- Redirect to ACS by default to keep compatibilty with 2.4.7 and CSP restrict mode
- Replace Setup/UpgradeSchema with db_schema.xml
- Refactoring widget-api.js
- Check transaction ID before cancel
- Fallback billing phone
- Update redirection logo for cofidis cahoc
- Fix configuration failover deactivation path 

# 1.2.18
Released on 2023-12-13
- Clean i18n files and generalize LocalizedException
- Refactoring callPaylineApiDo methods and limit call to getWebPaymentDetails

# 1.2.17
Released on 2023-10-26
- Remove use of Magento\Quote\Api\Data\TotalsInterface
- Fix warning "Property declared dynamically" on PaymentManagement

# 1.2.16
Released on 2023-08-18
- Compatibility Magento 2.4.6 (remove Zend_Validate)
- Fix setup class UpdgradeIncrementIdToken


# 1.2.15
Released on 2022-11-30
- Fix tunnel loader
- Add several workflow to manage doWebPayment token usage
- Add configuration for customPaymentPageCode
- Add buyer information

# 1.2.14
Released on 2022-07-07
- Fix upgrade scripts
- Add debug level configuration
- Call GWPD to stop Payline notification
- Catch duplicate return to avoid cancel order
- Remove Magento_Paypal dependency
- Add domains to csp_whitelist.xml


# 1.2.13
Released on 2022-01-24
- Automatically call refund or reset API on amount error 
- Add full management wallet from customer account
- Improvement configuration screen
- Add configuration parameters
  - To manage behavior on payment error 
  - To disable failover mechanism
  - Default category setting
- Fix log path for SDK
- Fix fr_FR translations
- Fix Payline version API to 21 (revert from 1.2.12)

# 1.2.12
Released on 2021-08-01
- Add EQUENS payment
- Add configuration to manager technical errors
- Prevent multiple transaction for one order
- Prevent cancelled order creation when no Payline transaction

# 1.2.11
Released on 2021-06-30
- Fix multi store configuration
- Fix error on product without category

# 1.2.10
Released on 2021-03-30
- Add csp_whitelist.xml
- Empty mini cart after payment
- Remove ascci encoding and substr
- Increase Payline version API from 16 to 21 

# 1.2.9
Released on 2020-10-31
- Add NX payment
- Fix on setup script to prevent error using magento/data-migration-tool
- System config field "Delivery expected delay" is required
- Compatible Magento >= 2.3

# 1.2.8.1
Released on 2020-08-31
- Change comment documention URLs
- Fix sur language_code for Oney 
- Move config constants 

# 1.2.8
Released on 2020-07-13
- Fix on wallet with Paypal contract
- Fix default config path with 4 levels

# 1.2.7
Released on 2020-06-25
- Add new payment method: Oney
- Fix on wallet with Paypal contract
- Fix default config path with 4 levels

# 1.2.6
Released on 2020-05-11
- Handle return errors messages into checkout 
- Improve log level management
- Fix multi point of sell contracts 
