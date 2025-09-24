function login()
{
    let email = $("#email").val()
    let pwd = $("#pwd").val()

    $.ajax({
        url: 'exe/login.php',
        method: 'POST',
        data: {email: email, pwd: pwd},
        success: function(x){
            if(x == 1){
                Swal.fire(
                    'Failed!',
                    "Empty form",
                    'error'
                    )
            }else if(x == 2){
                Swal.fire(
                    'Failed!',
                    "You're not the admin",
                    'error'
                    )
            }else if(x == 3){
                Swal.fire(
                    'Failed!',
                    "Wrong Password",
                    'error'
                    )
            }else if(x == '4'){
                Swal.fire(
                    'Failed!',
                    "Something went wrong!",
                    'error'
                    )
            }else if(x == 5){
                Swal.fire(
                    'Success!',
                    "Login Successfully",
                    'success'
                    )
                window.location.replace("doctor/dashboard.php")
            }else if(x == 6){
                Swal.fire(
                    'Success!',
                    "Login Successfully",
                    'success'
                    )
                window.location.replace("nurse/dashboard.php")
            }else if(x == 10){
                Swal.fire(
                    'Failed!',
                    "New device detected!",
                    'error'
                    )
                alert("We send a verification mail through your email address. Please verify if it's really you!")
                window.location.replace("index.php")
            }else if(x == 'boss'){
                Swal.fire(
                    'Success!',
                    "Login Successfully",
                    'success'
                    )
                window.location.replace("user/dashboard.php")
            }else if(x == 'login_error'){
                Swal.fire(
                    'Invalid Account!',
                    "We temporary blocked your account because of 3 consecutive failed password. You need to contact the Admin to fix this problem. ",
                    'error'
                    )
            }
        }
    })
}// End of login() function


function submit()
{
    let email = $("#email").val()

    $.ajax({
        url: '../exe/forgot.php',
        method: 'POST',
        data: {email: email},
        success: function(x){
            if(x == 1){
                Swal.fire(
                    'Failed!',
                    "Empty form",
                    'error'
                    )
            }else if(x == 2){
                Swal.fire(
                    'Failed!',
                    "You don't have an account",
                    'error'
                    )
            }else if(x == 3){
                Swal.fire(
                    'Failed!',
                    "Something went wrong!",
                    'error'
                    )
            }else if(x == 'success'){
                alert("The reset code was sent to this email address ("+email+")")
                window.location.replace("reset.php")
            }
        }
    })
}// End of function submit()

function reset()
{
    let email = $("#email").val()
    let otp = $("#otp").val()
    let code = $("#code").val()
    let pwd = $("#pwd").val()
    let pwdRepeat = $("#pwdRepeat").val()

    $.ajax({
        url: '../exe/reset.php',
        method: 'POST',
        data: {email: email, otp: otp, code: code, pwd: pwd, pwdRepeat: pwdRepeat},
        success: function(x){
            if(x == 1){
                Swal.fire(
                    'Failed!',
                    "Empty form",
                    'error'
                    )
            }else if(x == 2){
                Swal.fire(
                    'Failed!',
                    "Invalid Code!",
                    'error'
                    )
            }else if(x == 3){
                Swal.fire(
                    'Failed!',
                    "Password don't match to Repeat Password!",
                    'error'
                    )
            }else if(x == 4){
                Swal.fire(
                    'Failed!',
                    "Something went wrong!",
                    'error'
                    )
            }else if(x == 5){
                Swal.fire(
                    'Done',
                    "Reset Password successfully",
                    'success'
                    )
                window.location.replace("../index.php")
            }
        }
    })
}
// End of function reset()

