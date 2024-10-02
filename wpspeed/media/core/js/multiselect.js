/**
 * WPSpeed - performs several front-end optimizations for fast downloads
 *
 * @package   WPSpeed
 * @author    JExtensions Store <info@storejextensions.org>
 * @copyright Copyright (c) 2022 JExtensions Store / WPSpeed
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

var wpspeedMultiselect = (function ($) {
    $(document).ready(function () {

        var timestamp = getTimeStamp();
        var datas = [];
        //Get all the multiple select fields and iterate through each
        $('select.wpspeed-multiselect').each(function () {
            var el = $(this);

            datas.push({
                'id': el.attr('id'),
                'type': el.attr('data-wpspeed_type'),
                'param': el.attr('data-wpspeed_param'),
                'group': el.attr('data-wpspeed_group')
            });

        });

        var xhr = $.ajax({
            dataType: 'json',
            url: adminUtilities.wpspeed_ajax_url_multiselect + '&_=' + timestamp,
            data: {'data': datas},
            method: 'POST',
            success: function (response) {
                $.each(response.data, function (id, obj) {

                    const select = $("#" + id);

                    $.each(obj.data, function (value, option) {
                        select.append('<option value="' + value + '">' + option + '</option>');
                    });
                    
                    
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error returned from ajax function \'getmultiselect\''); 
                console.error('textStatus: ' + textStatus);
                console.error('errorThrown: ' + errorThrown);
                console.warn('response: ' + jqXHR.responseText);
            },
            complete: function () {
                //Remove all loading images
                $('img.wpspeed-multiselect-loading-image').each(function () {
                    $(this).remove();
                });
            }
        });
    });
})(jQuery);



