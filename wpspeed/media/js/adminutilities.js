/**
 * WPSpeed - Performs several front-end optimizations for fast downloads
 *
 * @package   WPSpeed
 * @author    JExtensions Store <info@storejextensions.org>
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

var adminUtilities = (function ($) {

    let wpspeed_ajax_url_optimizeimages = ajaxurl + '?action=optimizeimages';
    let wpspeed_ajax_url_multiselect = ajaxurl + '?action=multiselect';


    let configure_url = "options-general.php?page=wpspeed&view=configure";

    var submitForm = function () {
        document.getElementById('wpspeed-settings-form').submit();
    }

    return {
        //properties
        wpspeed_ajax_url_optimizeimages: wpspeed_ajax_url_optimizeimages,
        wpspeed_ajax_url_multiselect: wpspeed_ajax_url_multiselect,
        //methods
        submitForm: submitForm
    }

})(jQuery);