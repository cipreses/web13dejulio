jQuery(document).ready(function() {
    jQuery("#mo_saml_pricing_menu").click(function() {
      window.open("https://plugins.miniorange.com/wordpress-single-sign-on-sso#pricing","_blank");
    });
    jQuery('.logo-saml-cstm').click(function() { 
      var idp_name = jQuery('#mo_saml_identity_provider_identifier_name').val();
      var idp_specific_ads = JSON.parse(jQuery("#idp_specific").val());
      if(idp_specific_ads[idp_name]) { 
        jQuery('#mo_service').show();
        const idp_text = document.getElementById('idp_ads_check_idp_name');
        if (idp_text != null) {
          idp_text.innerText = idp_name;
        }
      }
      else {
        jQuery('#mo_service').hide();
      }         
      document.querySelector('#idp_scroll_saml').scrollIntoView();
    });
    let mo_idp = jQuery('#mo_saml_identity_provider_identifier').val();
    const idp_text = document.getElementById('idp_ads_check_idp_name');
    if(idp_text != null) {
      idp_text.innerText=mo_idp;
    }
});