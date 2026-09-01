window.onload = function() {
    var target_btn = document.getElementById("mo_saml_button");
    var before_element = document.querySelector("#loginform p");
    before_element.before(target_btn);
};   

document.getElementById('mo_saml_login_sso_button').addEventListener('click', function() {
    document.getElementById('saml_user_login_input').value = 'saml_user_login';
    document.getElementById('loginform').submit();
});
