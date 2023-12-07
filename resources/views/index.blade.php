<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TailorInch</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.css">
<link rel="stylesheet" href="{{ URL::to('/') }}/css/animate.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="{{ URL::to('/') }}/style.css"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
<!-- Font Google -->
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container"> <a class="navbar-brand navbar-logo" href="#"> <img src="{{ URL::to('/') }}/images/logo.png" alt="logo" class="logo-1"> </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="fas fa-bars"></span> </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"> <a class="nav-link" href="" data-scroll-nav="0">Home</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="1">About</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="2">Features</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="3">Team</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="4">Privacy & Policy</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="5">Faq</a> </li>
        <li class="nav-item"> <a class="nav-link" href="#" data-scroll-nav="6">Contact</a> </li>
      </ul>
    </div>
  </div>
</nav>
<!-- End Navbar --> 
<!-------Banner Start------->
<section class="banner" data-scroll-index='0'>
  <div class="banner-overlay">
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-sm-12">
          <div class="banner-text">
            <h2 class="white">Tailoring Management Platform</h2>
            <!-- <h6 class="white">This awesome template designed by <a href="http://w3Template.com" target="_blank" rel="dofollow" class="weblink">W3 Template</a>.</h6> -->
            <p class="banner-text white">The all-in-one tool for tailors. Manage customers, orders, measurements, and shop tasks seamlessly. Elevate your tailoring business effortlessly.</p>
            <ul>
              <li><a href="#"><img src="{{ URL::to('/') }}/images/appstore.png" class="wow fadeInUp" data-wow-delay="0.4s"/></a></li>
              <li><a href="https://play.google.com/store/apps/details?id=com.xtrapp.tailorinch"><img src="{{ URL::to('/') }}/images/playstore.png" class="wow fadeInUp" data-wow-delay="0.7s"/></a></li>
            </ul>
          </div>
        </div>
        <div class="col-md-4 col-sm-12"> <img src="{{ URL::to('/') }}/images/iphone-screen.png" class="img-fluid wow fadeInUp"/> </div>
      </div>
    </div>
  </div>
  <span class="svg-wave"> <img class="svg-hero" width="100%" src="{{ URL::to('/') }}/images/applight-wave.svg"> </span> </section>

<!-------Banner End-------> 

<!-------About End------->

<section class="about section-padding prelative" data-scroll-index='1'>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center">
          <h3>About</h3>
          <span class="line"></span>
          <p>Empowering Tailors to Streamline Operations Effortlessly</p>
        </div>
        <div class="section-content text-center">
          <div class="row">
            <div class="col-md-4">
              <div class="icon-box wow fadeInUp" data-wow-delay="0.2s"> <i class="fa fa-life-ring" aria-hidden="true"></i>
                <h5>Support</h5>
                <p>Experience dedicated assistance and guidance whenever you need it. Our responsive support ensures you're never alone in managing your tailor shop efficiently.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="icon-box wow fadeInUp" data-wow-delay="0.4s"> <i class="fa fa-mobile" aria-hidden="true"></i>
                <h5>Cross Platform</h5>
                <p>Access StitchWise from any device, anywhere. Enjoy the flexibility and convenience of a platform that adapts to your needs.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="icon-box wow fadeInUp" data-wow-delay="0.6s"> <i class="fa fa-bolt" aria-hidden="true"></i>
                <h5>Fast</h5>
                <p>Swift, intuitive, and efficient. StitchWise is designed to optimize your workflow, ensuring a speedy and seamless experience in managing your tailoring tasks.</p>
              </div>
            </div>
          </div>
          <!-- <a href="#" class="about-btn">Learn More</a> </div> -->
      </div>
    </div>
  </div>
</section>
<!-------About End-------> 

<!-------Video Start------->
<section class="video-section prelative text-center white">
  <div class="section-padding video-overlay">
    <div class="container">
      <h3>Watch Now</h3>
      <i class="fa fa-play" id="video-icon" aria-hidden="true"></i>
      <div class="video-popup">
        <div class="video-src">
          <div class="iframe-src">
            <iframe src="https://www.youtube.com/embed/Ku52zNnft8k?rel=0&amp;showinfo=0" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-------Video End-------> 

<!-------Features Start------->
<section class="feature section-padding" data-scroll-index='2'>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center">
          <h3>Features</h3>
          <span class="line"></span>
          <p>Elevating tailors' operations with a suite of seamless, integrated features.</p>
        </div>
        <div class="section-content text-center">
          <div class="row">
            <div class="col-md-4 col-sm-12">
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.2s">
                <div class="media-body text-right media-right-margin">
                  <h5>Customer Management</h5>
                  <p>Efficiently add, organize, and maintain customer profiles, keeping track of preferences and order history.</p>
                </div>
                <div class="media-right icon-border"> <span class="fa fa-bolt" aria-hidden="true"></span> </div>
              </div>
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.4s">
                <div class="media-body text-right media-right-margin">
                  <h5>Order and Dress Tracking</h5>
                  <p>Seamlessly manage dress orders, track progress, and monitor the status of each garment in production.</p>
                </div>
                <div class="media-right icon-border"> <span class="fa fa-battery-full" aria-hidden="true"></span> </div>
              </div>
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.6s">
                <div class="media-body text-right media-right-margin">
                  <h5>Measurements Repository</h5>
                  <p>Store precise measurements securely, ensuring accuracy and consistency for every customer order.</p>
                </div>
                <div class="media-right icon-border"> <span class="fa fa-wifi" aria-hidden="true"></span> </div>
              </div>
            </div>
            <div class="col-md-4 d-none d-md-block d-lg-block">
              <div class="feature-mobile"> <img src="{{ URL::to('/') }}/images/iphone-screen-with-shadow.png" class="img-fluid wow fadeInUp"/> </div>
            </div>
            <div class="col-md-4 col-sm-12">
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.2s">
                <div class="media-left icon-border media-right-margin"> <span class="fa fa-check-double" aria-hidden="true"></span> </div>
                <div class="media-body text-left">
                  <h5>Shop Administration</h5>
                  <p>Simplify shop management tasks, including inventory tracking, appointment scheduling, and financial management.</p>
                </div>
              </div>
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.4s">
                <div class="media-left icon-border media-right-margin"> <span class="fa fa-dollar-sign" aria-hidden="true"></span> </div>
                <div class="media-body text-left">
                  <h5>Payment Tracking</h5>
                  <p>Keep transparent records of payments received and outstanding, streamlining financial tracking.</p>
                </div>
              </div>
              <div class="media single-feature wow fadeInUp" data-wow-delay="0.6s">
                <div class="media-left icon-border media-right-margin"> <span class="fa fa-hdd" aria-hidden="true"></span> </div>
                <div class="media-body text-left">
                  <h5>Customization and Notifications</h5>
                  <p>Personalize services and send automated notifications to customers about order status updates.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-------Features End-------> 

<!-------Team Start------->
<section class="team section-padding" data-scroll-index='3'>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center">
          <h3>Our Team</h3>
          <span class="line"></span>
          <p>Meet the Dedicated Innovators Behind <b>TailorInch</b> A passionate team committed to revolutionizing the tailor's experience through technology and expertise</p>
        </div>
        <div class="section-content text-center">
          <div class="row" style='justify-content: center;'>
            <!-- <div class="col-md-4">
              <div class="team-detail wow bounce" data-wow-delay="0.2s"> <img src="images/user1.jpg" class="img-fluid"/>
                <h4>Nitu Singh</h4>
                <p>Marketing Specialist</p>
              </div>
            </div> -->
            <div class="col-md-4">
              <div class="team-detail wow bounce" data-wow-delay="0.4s"> <img src="images/user2.png" class="img-fluid"/>
                <h4>Raja Ram</h4>
                <p>Co-Founder & Developer</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="team-detail wow bounce" data-wow-delay="0.6s"> <img src="images/user3.jpg" class="img-fluid"/>
                <h4>Waris Waheed</h4>
                <p>Co-Founder & Developer</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-------Team End-------> 

<!-------Privacy Start------->
<section class="privacy section-padding" data-scroll-index='4' id="privacy-policy">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center white">
          <h3>Privacy & Policy</h3>
          <span class="line"></span>
          <p class="white">Our Privacy Policy outlines how we collect, use, and safeguard your information, ensuring transparency and trust in every interaction.</p>
        </div>
    
        <div class="section-content">
          <div class="row">
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Acceptance of Terms</h4>
              <p>By using TailorInch App, you agree to comply with and be bound by these Terms and Conditions. If you do not agree to these Terms and Conditions, please do not use the app.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Registration</h4>
              <p>You must provide accurate and complete information during the registration process.</p>
              <p>You are responsible for maintaining the confidentiality of your account and password.</p>
              <p>You are responsible for all activities that occur under your account.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Services Offered</h4>
              <p>TailorInch App provides a platform for tailors to manage their services to customers.</p>
              <p>Tailors are responsible for the quality and accuracy of their services.</p>
              <p>TailorInch App does not guarantee the availability of customers or the success of any transaction.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Fees and Payments</h4>
              <p>Tailors are entitled to collect fees for TailorInch App for using the platform.</p>
              <p>Fees and payment terms are outlined in our current contract due to no any paid services.</p>
              <p>TailorInch App may change its fee structure at any time with prior notice for using services for using paid services once provided.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Conduct and Content</h4>
              <p>Tailors must not use TailorInch App to engage in any unlawful, offensive, or harmful activities.</p>
              <p>You are solely responsible for the content you post on the app.</p>
              <p>TailorInch App reserves the right to remove or block any content that violates these terms.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Intellectual Property</h4>
              <p>TailorInch App owns all intellectual property rights in the app and its content. Tailors will not use TailorInch App's name or logo without permission.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Privacy</h4>
              <p>TailorInch App collects and uses personal information as described in its Privacy Policy.</p>
              <p>Tailors must respect the privacy of customers and not misuse their personal information.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Termination</h4>
              <p>TailorInch App may terminate your account at its discretion, with or without cause.</p>
              <p>Tailors may terminate their account by following the process outlined on the app or they will be provided with their customers required data.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Disclaimers</h4>
              <p>TailorInch App is not responsible for the actions or behavior of customers or tailors.</p>
              <p>TailorInch App is not liable for any loss or damages resulting from the use of the app.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Governing Law and Dispute Resolution</h4>
              <p>These terms are governed by the laws of Pakistan & Global Jurisdiction.</p>
              <p>Any disputes shall be resolved through arbitration or mediation as outlined in a separate agreement.</p>
            </div>
            <div class="col-md-6 privacy-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Changes to Terms</h4>
              <p>TailorInch App may change these Terms and Conditions at any time with prior notice. It is your responsibility to review and understand the updated terms.</p>
              <p>By using TailorInch App, you acknowledge that you have read, understood, and agreed to these Terms and Conditions. If you have any questions, please contact contact@tailorinch.com.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-------Privacy End-------> 

<!-------FAQ Start------->
<section class="faq section-padding prelative" data-scroll-index='5'>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center">
          <h3>Frequently Asked Questions</h3>
          <span class="line"></span>
          <p>Your comprehensive solution for tailoring management, offering seamless operations and empowering tailors to elevate their craft and business effortlessly.</p>
        </div>
        <div class="section-content">
          <div class="row">
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Is TailorInch suitable for both individual tailors and tailor shops?</h4>
              <p>TailorInch caters to both individual tailors and larger establishments, offering customized solutions for various business sizes.</p>
            </div>
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.2s">
              <h4>Can I access TailorInch from my smartphone and computer?</h4>
              <p>Yes, TailorInch is accessible on multiple devices, ensuring flexibility and convenience in managing your tailoring tasks.</p>
            </div>
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.4s">
              <h4>How secure is the customer data stored on TailorInch?</h4>
              <p>Customer data security is paramount. TailorInch employs robust encryption and security measures to safeguard all information.</p>
            </div>
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.4s">
              <h4>Is there training available for using TailorInch effectively?</h4>
              <p>Absolutely, TailorInch offers comprehensive guides and support to ensure users make the most of the platform's capabilities.</p>
            </div>
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>Can I customize notifications and alerts for my customers?</h4>
              <p>Yes, TailorInch allows personalized communication, enabling tailor-made notifications for your customers about their orders.</p>
            </div>
            <div class="col-md-6 faq-content wow fadeInUp" data-wow-delay="0.6s">
              <h4>What happens if I encounter issues while using TailorInch?</h4>
              <p>TailorInch provides dedicated customer support via live chat, email, and comprehensive documentation to assist users promptly.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-------FAQ End-------> 

<!-------Contact Start------->
<section class="contact section-padding" data-scroll-index='6'>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center">
          <h3>Get in touch with TailorInch</h3>
          <span class="line"></span>
          <p>We're here to answer your queries, provide assistance, and welcome your feedback for an optimized tailoring experience.</p>
        </div>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-8">
              <form id="contact_form" action="">
                <div class="row">
                  <div class="col">
                    <input type="text" id="your_name" class="form-input w-100" name="full-name" placeholder="Full Name" required>
                  </div>
                  <div class="col">
                    <input type="email" id="email" class="form-input w-100" name="email" placeholder="Email" required>
                  </div>
                </div>
                <input type="text" id="subject" class="form-input w-100" name="subject" placeholder="Subject">
                <textarea class="form-input w-100" id="message" placeholder="Message" name="message"></textarea>
                <button class="btn-grad w-100 text-uppercase" type="submit" name="button">submit</button>
              </form>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-4">
              <div class="contact-info white">
                <div class="contact-item media"> <i class="fa fa-map-marker-alt media-left media-right-margin"></i>
                  <div class="media-body">
                    <p class="text-uppercase">Address</p>
                    <p class="text-uppercase">Karachi, Pakistan</p>
                  </div>
                </div>
                <div class="contact-item media"> <i class="fa fa-mobile media-left media-right-margin"></i>
                  <div class="media-body">
                    <p class="text-uppercase">Phone</p>
                    <p class="text-uppercase"><a class="text-white" href="tel:+923053044607">+92 3053044607</a> </p>
                  </div>
                </div>
                <div class="contact-item media"> <i class="fa fa-envelope media-left media-right-margin"></i>
                  <div class="media-body">
                    <p class="text-uppercase">E-mail</p>
                    <p class=""><a class="text-white" href="mailto:contact@tailorinch.com">contact@tailorinch.com</a> </p>
                  </div>
                </div>
                <div class="contact-item media"> <i class="fa fa-clock media-left media-right-margin"></i>
                  <div class="media-body">
                    <p class="text-uppercase">Working Hours</p>
                    <p class="text-uppercase">Mon-Fri 9.00 AM to 6.00PM PST.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-------Contact End-------> 

<!-------Download End------->
<section class="download section-padding">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="sectioner-header text-center white">
          <h3>Download Our App</h3>
          <span class="line"></span>
          <p class="white">Enhance your tailoring experience with TailorInch. Download our app for seamless management of orders, customer profiles, measurements, and shop tasks, all at your fingertips.</p>
        </div>
      </div>
      <div class="col-md-12">
        <div class="section-content text-center">
          <ul>
            <li><a href=""><img src="images/appstore.png" class="wow fadeInUp" data-wow-delay="0.4s"/></a></li>
            <li><a href="https://play.google.com/store/apps/details?id=com.xtrapp.tailorinch"><img src="images/playstore.png" class="wow fadeInUp" data-wow-delay="0.7s"/></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-------Download End------->

<footer class="footer-copy">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <p>2023 &copy; TailorInch.</p>
      </div>
    </div>
  </div>
</footer>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> 
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script> 
<script src="https://cdn.jsdelivr.net/bxslider/4.2.12/jquery.bxslider.min.js"></script> 
<!-- scrollIt js --> 
<script src="js/scrollIt.min.js"></script> 
<script src="js/wow.min.js"></script> 
<script>
	wow = new WOW();
	wow.init();
$(document).ready(function(e) {

	$('#video-icon').on('click',function(e){
	e.preventDefault();
	$('.video-popup').css('display','flex');
	$('.iframe-src').slideDown();
	});
	$('.video-popup').on('click',function(e){
	var $target = e.target.nodeName;
	var video_src = $(this).find('iframe').attr('src');
	if($target != 'IFRAME'){
	$('.video-popup').fadeOut();
	$('.iframe-src').slideUp();
	$('.video-popup iframe').attr('src'," ");
	$('.video-popup iframe').attr('src',video_src);
	}
	});

	$('.slider').bxSlider({
	pager: false
	});
});
    
$(window).on("scroll",function () {

	var bodyScroll = $(window).scrollTop(),
	navbar = $(".navbar");
	
	if(bodyScroll > 50){
	$('.navbar-logo img').attr('src','{{ URL::to('/') }}/images/logo-black.png');
	navbar.addClass("nav-scroll");

}else{
	$('.navbar-logo img').attr('src','{{ URL::to('/') }}/images/logo.png');
	navbar.removeClass("nav-scroll");
}

});
$(window).on("load",function (){
	var bodyScroll = $(window).scrollTop(),
	navbar = $(".navbar");
	
	if(bodyScroll > 50){
	$('.navbar-logo img').attr('src','{{ URL::to('/') }}/images/logo-black.png');
	navbar.addClass("nav-scroll");
	}else{
	$('.navbar-logo img').attr('src','{{ URL::to('/') }}/images/logo-white.png');
	navbar.removeClass("nav-scroll");
	}

	$.scrollIt({
	
	easing: 'swing',      // the easing function for animation
	scrollTime: 900,       // how long (in ms) the animation takes
	activeClass: 'active', // class given to the active nav element
	onPageChange: null,    // function(pageIndex) that is called when page is changed
	topOffset: -63
	});
});

</script>
</body>
</html>
