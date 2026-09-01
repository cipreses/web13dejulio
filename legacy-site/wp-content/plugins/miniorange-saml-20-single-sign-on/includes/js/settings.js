jQuery(document).ready(function() {

    if(jQuery("#acc-tab-form").is(':visible')) {
        let is_already_reg = sessionStorage.getItem('mo_saml_already_reg');
        if(is_already_reg!==null && is_already_reg==="true"){
            MoSamlGotoLoginPage();
        }else{
            MoSamlBacktoRegisterPage();
        }
    };

    //dismiss welcome modal
    jQuery("#mo_saml_modal_dismiss").click(function() {
        let getting_started_modal = document.getElementById("mo-saml-getting-started");
        getting_started_modal.style.display = "none";    
    });
    
    //show and hide attribute mapping instructions
    jQuery("#toggle_am_content").click(function() {
        jQuery("#show_am_content").toggle();
    });
    jQuery('.updated').show();
    jQuery('.updated').insertBefore('#mo-saml-tabs');
    jQuery('.error').show();
    jQuery('.error').insertBefore('#mo-saml-tabs');

    //Licensing Plans
    jQuery('.goto-opt a').click(function() {
        jQuery('.goto-active').removeClass('goto-active');
        jQuery(this).addClass('goto-active');
    });
    jQuery('.tab').click(function() {
        jQuery('.handler').hide();
        jQuery('.' + jQuery(this).attr('id')).css({'display': 'flex'});
        jQuery('.active').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.' + jQuery(this).attr('id') + '-rot').css('transform', 'rotateY(0deg)');
        jQuery('.common-rot').not('.' + jQuery(this).attr('id') + '-rot').css({
            'transform': 'rotateY(180deg)',
            'transition': '0.3s'
        });
        jQuery('.cp-single-site, .cp-multi-site').removeClass('mo-saml-bootstrap-show');
        jQuery('.' + jQuery(this).attr('id') + ' .clk-icn i').removeClass('fa-expand-alt').addClass('fa-expand-alt');
    });
    jQuery('.clk-icn').click(function() {
        jQuery(this).find('i').toggleClass('fa-times fa-expand-alt')
    });
    jQuery('.goto-opt a').click(function(e) {
        var href = jQuery(this).attr("href");
        if (href && href.startsWith("#")) {
            e.preventDefault();
            var offsetTop = href === "#" ? 0 : jQuery(href).offset().top - 180;
            jQuery('html, body').stop().animate({
                scrollTop: offsetTop
            }, 300);
        }
    });
    const toggles = document.querySelectorAll(".faq-toggle");
    toggles.forEach((toggle) => {
        toggle.addEventListener("click", () => {
            toggle.parentNode.classList.toggle("active");
        });
    });
    jQuery(".tab-us").css('border-bottom', '1px solid #2f4f4f');
    jQuery(".instances").css('border-bottom', '4px solid #2f4f4f');
    jQuery(".integration-section").css('display', 'none');
    jQuery("#instances").css('display', 'block');
    jQuery(".multi-network").click(function() {
        jQuery(".integration-section").css('display', 'none');
        jQuery("#multi-network").css('display', 'block');
        jQuery(".multi-network").css('border-bottom', '4px solid #2f4f4f');
    });
    jQuery(".instances").click(function() {
        jQuery(".integration-section").css('display', 'none');
        jQuery("#instances").css('display', 'block');
        jQuery(".instances").css('border-bottom', '4px solid #2f4f4f');
    });
    jQuery(".multi-idp").click(function() {
        jQuery(".integration-section").css('display', 'none');
        jQuery("#multi-idp").css('display', 'block');
        jQuery(".multi-idp").css('border-bottom', '4px solid #2f4f4f');
    });
    jQuery(".multi-network,.instances,.multi-idp").hover(function() {
        jQuery(".tabs11,.tab-us").css('border-bottom', '1px solid #2f4f4f');
    });
    jQuery(".intg-tab").click(function() {
        jQuery(".intg-tab").removeClass('active-tab');
        jQuery(this).addClass('active-tab');
    });
    jQuery(window).scroll(function() {
        var scrollDistance = jQuery(window).scrollTop();
        var num = -1;

        jQuery('.saml-scroll').each(function(i) {
            if (jQuery(this).offset().top - 450 <= scrollDistance) {
                num = i;
            }
        });
        if (num != -1) {
            jQuery('.goto-opt a.goto-active').removeClass('goto-active');
            jQuery('.goto-opt a').eq(num).addClass('goto-active');
        } else {
            jQuery('.goto-opt a.goto-active').removeClass('goto-active');
        }
    }).scroll();

    var acc = document.getElementsByClassName("faq");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function () {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;

            });
        }

    // sp-tab-switch
    jQuery('.mo-saml-sp-tab-container a').click(function(event) {
        var activeTab = jQuery(this).attr("href");

        if (!activeTab || !activeTab.startsWith("#")) {
            return;
        }
        
        event.preventDefault();

        jQuery('.mo-saml-sp-tab-container .switch-tab-sp a').closest('li').removeClass("mo-saml-current");
        jQuery(this).closest('li').addClass("mo-saml-current");

        // display only active tab content
        jQuery('.mo-saml-tab-content').not(activeTab).css("display", "none");
        jQuery(activeTab).fadeIn();

    });
    jQuery('.contact-us-cstm').click(function() {
        jQuery('.contact-form-cstm').addClass('contact-form-cstm-slide');
        jQuery('.contact-form-cstm').removeClass('contact-form-cstm-slide1');
    });
    jQuery('.cls-cstm').click(function() {
        jQuery('.contact-form-cstm').addClass('contact-form-cstm-slide1');
        jQuery('.contact-form-cstm').removeClass('contact-form-cstm-slide');
    });

    function MoSamlGotoLoginPage(){
        jQuery('.mo-saml-reg-text-field').prop('disabled', true);
        jQuery('.mo-saml-login-text-field').prop('disabled', false);
        jQuery('.mo-saml-reg-field , #mo_saml_reg_btn, #mo_saml_goto_login').hide();
        jQuery('.mo-saml-already-reg-field ').show().css('display', 'flex');
        jQuery('#mo_saml_reg_login_btn , #mo_saml_reg_back_btn').show().css('display', 'inline');
        jQuery('.mo-saml-why-reg-txt').hide();
        jQuery('.mo-saml-why-login-txt').show();
    }
    function MoSamlBacktoRegisterPage(){
        jQuery('.mo-saml-reg-text-field').prop('disabled', false);
        jQuery('.mo-saml-login-text-field').prop('disabled', true);
        jQuery('.mo-saml-reg-field').show().css('display', 'flex');
        jQuery('#mo_saml_reg_btn, #mo_saml_goto_login').show();
        jQuery('.mo-saml-already-reg-field ,  #mo_saml_reg_login_btn , #mo_saml_reg_back_btn').hide();
        jQuery('.mo-saml-why-reg-txt').show();
        jQuery('.mo-saml-why-login-txt').hide();
    }
    jQuery('#mo_saml_goto_login').click(function() {
        sessionStorage.setItem('mo_saml_already_reg', "true");
        MoSamlGotoLoginPage();
    });
    jQuery('#mo_saml_reg_back_btn').click(function() {
        sessionStorage.setItem('mo_saml_already_reg', "false");
        MoSamlBacktoRegisterPage();
    });

    if(document.getElementById("contact_us_phone"))
        jQuery("#contact_us_phone").intlTelInput();

    jQuery("#mo_saml_mo_idp").click(function() {
        jQuery("#mo_saml_mo_idp_form").submit();
    });
    var mo_saved_idp = jQuery('#mo_saml_identity_provider_identifier_name').val();
    var mo_saved_idp_details = jQuery('#mo_saml_identity_provider_identifier_details').val();
    if ((mo_saved_idp != undefined && mo_saved_idp != null && mo_saved_idp != '') && (mo_saved_idp_details != undefined && mo_saved_idp_details != null && mo_saved_idp_details != '')) {
        var details = JSON.parse(jQuery('#mo_saml_identity_provider_identifier_details').val());
        var a_href = details['idp_guide_link'];
        var video_link = details['idp_video_link'];
        var idp_name = jQuery('#mo_saml_identity_provider_identifier_name').val();
        var image_src = details['image_src'];
        mo_saml_get_idp_data(idp_name, image_src, video_link, a_href);
    }

    // Click to select IDP JS
    jQuery('.logo-saml-cstm').click(function() {
        var a_href = jQuery(this).find('a').data('href');
        var video_link = jQuery(this).find('a').data('video');
        var idp_name = jQuery(this).children().find('h6').text();
        var image_src = jQuery(this).find('img').attr('src');

        mo_saml_get_idp_data(idp_name, image_src, video_link, a_href);
        document.querySelector('#idp_scroll_saml').scrollIntoView();
    });

    function mo_saml_get_idp_data(idp_name, image_src, video_link, a_href) {
        var idp_specific_ads_text = JSON.parse(jQuery("#idp_specific_ads").val());
        jQuery('#mo_saml_identity_provider_identifier_name').val(idp_name);
        if (typeof idp_specific_ads_text[idp_name] != "undefined") {
            setTimeout(function() {
                jQuery('#mo_saml_identity_provider_identifier_name').val(idp_name);
                jQuery('#mo-saml-ads-text').show();
                jQuery('#mo-saml-ads-cards-text').html(idp_specific_ads_text[idp_name]["Text"]);
                jQuery('#mo-saml-ads-head').text(idp_specific_ads_text[idp_name]["Heading"]);
                jQuery('#ads-text-link').text(idp_specific_ads_text[idp_name]["Link_Title"]);
                jQuery('#ads-text-link').attr("href", idp_specific_ads_text[idp_name]["Link"]);
                if (idp_specific_ads_text[idp_name]["Know_Title"] && idp_specific_ads_text[idp_name]["Know_Link"]) {
                    jQuery('#ads-knw-more-link').css('display', 'block');
                    jQuery('#ads-knw-more-link').text(idp_specific_ads_text[idp_name]["Know_Title"]);
                    jQuery('#ads-knw-more-link').attr("href", idp_specific_ads_text[idp_name]["Know_Link"]);
                } else {
                    jQuery('#ads-knw-more-link').css('display', 'none');
                }
            }, 0);
        } else {
            jQuery('#mo-saml-ads-text').hide();
        }

        var video_link_id = video_link.split("?v=")[1];
        if (video_link_id == "" || video_link_id == null || video_link_id.length == 0) {
            jQuery('#saml_idp_video_link').hide();
        } else {
            jQuery('#saml_idp_video_link').show();
            jQuery('#saml_idp_video_link').attr('href', video_link);
        }
        jQuery('#mo_saml_selected_idp_div').show();
        jQuery('.hide-hr').show();

        jQuery('#mo_saml_selected_idp_icon_div img').attr('src', image_src);
        jQuery('#saml_idp_guide_link').attr('href', a_href);
    }

    jQuery('#mo-saml-ads-text').hide();
    jQuery("#mo_saml_search_idp_list").on("keyup", function() {
        var value = jQuery(this).val().toLowerCase();
        var active = 0;
        jQuery(".logo-saml-cstm").filter(function() {
            if (jQuery(this).text().toLowerCase().indexOf(value) > -1)
                active = 1;
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value) > -1);
            jQuery('.show-msg').css('display', 'none');
        });
        if (active == 0) {
            jQuery('.logo-saml-cstm[data-idp="gilfhNFYsgc"]').show();
            jQuery('.show-msg').css('display', 'block');
        }
    });
    jQuery('#saml_setup_call').change(function() {
        if (jQuery(this).is(":checked")) {
            jQuery('#call_setup_dets').show();
        } else {
            jQuery('#call_setup_dets').hide();
        }
    });
    displayWelcomePage();
    checkUploadMetadataFields();

    
    
});
jQuery(function() {
    jQuery("#call_setup_dets").hide();
    if(document.getElementById("js-timezone"))
        jQuery("#js-timezone").select2();

    jQuery("#saml_setup_call").click(function() {
        if (jQuery(this).is(":checked")) {
            jQuery("#call_setup_dets").show();
            document.getElementById("js-timezone").required = true;
            document.getElementById("datepicker").required = true;
            document.getElementById("timepicker").required = true;
            document.getElementById("mo_saml_query").required = false;

            jQuery("#datepicker").datepicker("setDate", +1);
            jQuery('#timepicker').timepicker('option', 'minTime', '00:00');

        } else {
            jQuery("#call_setup_dets").hide();
            document.getElementById("timepicker").required = false;
            document.getElementById("datepicker").required = false;
            document.getElementById("js-timezone").required = false;
            document.getElementById("mo_saml_query").required = true;
        }
    });
    if(document.getElementById("datepicker")){
        jQuery("#datepicker").datepicker({
            minDate: +1,
            dateFormat: 'M dd, yy'
        });
    }
    if(document.getElementById("timepicker")){
        jQuery('#timepicker').timepicker({
            timeFormat: 'H:i',
            interval: 30,
            minTime: new Date(),
            disableTextInput: true,
            dynamic: false,
            dropdown: false,
            scrollbar: true,
            forceRoundTime: true
        });
    }

    function mo_saml_valid_query(f) {
        !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
            /[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
    }
});



function mo_saml_show_test_window() {
    var url = jQuery('#mo-saml-test-window-url').val();
    var myWindow = window.open(url, "TEST SAML IDP", "scrollbars=1 width=800, height=600");
}

function redirect_to_attribute_mapping() {
    var url = jQuery('#mo-saml-attribute-mapping-url').val();
    window.location.href = url;
}

function redirect_to_service_provider() {
    var url = jQuery('#mo-saml-service-provider-url').val();
    window.location.href = url;
}

function redirect_to_redi_sso_link() {
    var url = jQuery('#mo-saml-redirect-sso-url').val();
    window.location.href = url;
}

function copyToClipboard(copyButton, element, copyelement) {
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    temp.remove();
    jQuery(copyelement).text("Copied");

    jQuery(copyButton).mouseout(function() {
        jQuery(copyelement).text("Copy to Clipboard");
    });
}

function displayWelcomePage() {
    let getting_started_modal = document.getElementById("mo-saml-getting-started");
    let modal = document.getElementById("mo_modal_value");
    let modal_value = '';
    if(modal)
        modal_value = modal.value;
    if (modal_value.length == 0 || modal_value != 1) {
        if(getting_started_modal)
            getting_started_modal.style.display = "block";
    }
}

function highlightAddonSubmenu() {
    jQuery(document).ready(function() {
        jQuery('#mo_saml_addons_submenu').parent().parent().parent().find('li').removeClass('current');
        jQuery('#mo_saml_addons_submenu').parent().parent().addClass('current');
    });
}
function checkUploadMetadataFields() {
    var fileField = jQuery("#metadata_file");
    var urlField = jQuery("#metadata_url");

    if (fileField.val() == "" && urlField.val() == "")
    {
        fileField.prop("required", true);
        urlField.prop("required", true);
    }
    else
    {
        fileField.prop("required", false);
        urlField.prop("required", false);
    }
}

function checkMetadataFile() {
    jQuery("#metadata_file").prop("required",true);
    jQuery("#metadata_url").prop("required",false);
    jQuery("#metadata-submit-button").click();
}
function checkMetadataUrl() {
    jQuery("#metadata_file").prop("required",false);
    jQuery("#metadata_url").prop("required",true);
    jQuery("#metadata-submit-button").click();
}

function addCertificateErrorClass() {
    var error = jQuery(".error").text();
    if (error.includes("X.509")) {
        jQuery("#saml_x509_certificate").addClass("mo-saml-error-box");
        jQuery(".mo-saml-error-tip").show();
        jQuery('html, body').animate({
            scrollTop: jQuery('#saml_issuer').offset().top
        }, 'slow');
        jQuery(function() {
            setTimeout(function() {
                jQuery(".mo-saml-error-tip").hide(100);
            }, 5000);
        });
    }
}

function removeCertificateErrorClass() {
    if(jQuery("#saml_x509_certificate").val() != "") {
        jQuery("#saml_x509_certificate").removeClass("mo-saml-error-box");
    }
}

function toggleFAQ(index){
    var tog = jQuery(".faq")[index];
        tog.classList.toggle("active"); 
}

function moSamlToggleSSOButtonCustomize() {
    var content = document.getElementById('mo-saml-sso-button-customize-table');
    var icon = document.getElementById('mo-saml-drop-dwn');
    var label = document.getElementById('mo-saml-sso-button-toggle-text');
    var hint = document.getElementById('mo-saml-sso-button-hint');
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
        if (label) { label.textContent = 'View Less'; }
        if (hint) { hint.style.display = 'none'; }
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
        if (label) { label.textContent = 'View More'; }
        if (hint) { hint.style.display = ''; }
    }
}

jQuery(document).ready(function($){
    function updateTooltipVisibility($wrapper){
        var $field = $wrapper.find('input, textarea').first();
        var hasValue = $.trim($field.val()) !== '';
        if(hasValue){ $wrapper.find('.mo-saml-tooltip').hide(); } else { $wrapper.find('.mo-saml-tooltip').show(); }
    }
    // Show icons initially for all inputs (even if pre-filled)
    $('.mo-saml-input-with-tooltip .mo-saml-tooltip').show();
    // Hide on user input/change when field has value
    $('.mo-saml-input-with-tooltip').each(function(){
        var $w = $(this);
        $w.find('input, textarea').on('input change', function(){ updateTooltipVisibility($w); });
    });
    // Certificate tooltip is outside its field; manage it separately
    var $certField = $('#saml_x509_certificate');
    var $certTipWrap = $('#mo-saml-cert-tooltip');
    if($certField.length && $certTipWrap.length){
        $certTipWrap.show();
        $certField.on('input change', function(){
            var hasValue = $.trim($certField.val()) !== '';
            if(hasValue){ $certTipWrap.hide(); } else { $certTipWrap.show(); }
        });
    }
});