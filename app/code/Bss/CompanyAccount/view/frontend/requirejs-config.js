/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
var config = {
    config: {
        mixins: {
            "Bss_MultiWishlist/js/bss_wishlist" : {
                "Bss_CompanyAccount/js/multi-wishlist/bss-wishlist": true
            },
            "Magento_Checkout/js/view/billing-address/list": {
                "Bss_CompanyAccount/js/view/billing-address/list-mixin": true
            }
        }
    },
    map: {
        '*': {
            bssTreeJs: "Bss_CompanyAccount/plugins/tree-js/jstree.min"
        }
    }
};
