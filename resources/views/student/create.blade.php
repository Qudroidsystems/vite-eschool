
@extends('layouts.master')
@section('content')


           <!--begin::Main-->
        <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
            <!--begin::Content wrapper-->
            <div class="d-flex flex-column flex-column-fluid">

<!--begin::Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar  py-3 py-lg-6 " >

        <!--begin::Toolbar container-->
    <div id="kt_app_toolbar_container" class="app-container  container-xxl d-flex flex-stack ">



<!--begin::Page title-->
<div  class="page-title d-flex flex-column justify-content-center flex-wrap me-3 ">
<!--begin::Title-->
<h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
   Student Management
        </h1>
<!--end::Title-->


    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                                <a href="../index.html" class="text-muted text-hover-primary">
                          Student Managemt                           </a>
                                        </li>
                            <!--end::Item-->
                                <!--begin::Item-->
                <li class="breadcrumb-item">
                    <span class="bullet bg-gray-400 w-5px h-2px"></span>
                </li>
                <!--end::Item-->

                        <!--begin::Item-->
                                <li class="breadcrumb-item text-muted">
                                               Add Student Record                                            </li>
                            <!--end::Item-->

                </ul>
    <!--end::Breadcrumb-->
</div>
<!--end::Page title-->
@if ($errors->any())
<div class="alert alert-danger">
<strong>Whoops!</strong> There were some problems with your input.<br><br>
<ul>
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</ul>
</div>
@endif

@if (\Session::has('status'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
{{ \Session::get('status') }}
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (\Session::has('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
{{ \Session::get('success') }}
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

    </div>
    <!--end::Toolbar container-->
</div>
<!--end::Toolbar-->

<!--begin::Content-->
<div id="kt_app_content" class="app-content  flex-column-fluid " >


    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container ">


                                <!--begin::Actions-->
                                <div class="d-flex align-items-center gap-2 gap-lg-3">


                                    <!--begin::Secondary button-->


                                    <!--begin::Primary button-->
                                        <a href="{{ route('student.index') }}" class="btn btn-sm fw-bold btn-primary" >
                                        << Back        </a>
                                    <!--end::Primary button-->
                                    </div>
                                    <!--end::Actions-->

<!--begin::Basic info-->
<div class="card mb-5 mb-xl-10">
<!--begin::Card header-->
<div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
    <!--begin::Card title-->
    <div class="card-title m-0">
        <h3 class="fw-bold m-0">Student Details</h3>
    </div>
    <!--end::Card title-->
</div>

<!--begin::Card header-->
@if (count($errors) > 0)
<div class="row animated fadeInUp">
      @if (count($errors) > 0)
<div class="alert alert-warning fade in">
<a href="#" class="close" data-dismiss="alert">&times;</a>
    <strong>Opps!</strong> Something went wrong, please check below errors.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
 </div>
 @endif
</div>
   @endif
<!--begin::Content-->
<div id="kt_account_settings_profile_details" class="collapse show">
    <!--begin::Form-->
    <form id="kt_account_profile_details_form" class="form" method="POST"
    enctype="multipart/form-data" action="{{ route('student.store') }}" >
      @csrf
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <input type="hidden" name="registeredBy" value="{{ Auth::user()->id}}">
            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">

                    <!--begin::Image input-->
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{asset('assets/assets/media/svg/avatars/blank.svg')  }})">
                        <!--begin::Preview existing avatar-->

                        <div class="image-input-wrapper w-125px h-125px"></div>
                        <!--end::Preview existing avatar-->

                        <!--begin::Label-->
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                            <!--begin::Inputs-->
                            <input type="file" name="avatar" id="avatar" accept=".png, .jpg, .jpeg" required/>
                            <input type="hidden" name="avatar_remove"/>
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->

                        <!--begin::Cancel-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        <!--end::Cancel-->

                        <!--begin::Remove-->
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>                            </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->

                    <!--begin::Hint-->
                    <div class="form-text">Allowed file types:  png, jpg, jpeg.</div>
                    <!--end::Hint-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Admission No</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <!--begin::Row-->
                    <div class="row">
                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="admissionNo" id="admissionNo" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Admission Number"  />
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Title</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">

                    <select  class="form-control form-control-lg form-control-solid" name="title" id="title">
                        <option value="" selected>Select Title </option>
                        <option value="Mr">Mr</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Miss">Miss</option>
                    </select>

                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Full Name</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8">
                    <!--begin::Row-->
                    <div class="row">
                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="firstname" id="firstname" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="First name"  />
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-lg-6 fv-row">
                            <input type="text" name="lastname" id="lastname" class="form-control form-control-lg form-control-solid" placeholder="Last name" />
                        </div>
                        <!--end::Col-->



                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label  fw-semibold fs-6"></label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-6 fv-row">
                    <input type="text" name="othername" id="othername" class="form-control form-control-lg form-control-solid" placeholder="Other names"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">
                    Gender
                </label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <!--begin::Options-->
                    <div class="d-flex align-items-center mt-3">
                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                            <input class="form-check-input" name="gender"  type="radio" value="Male" required />
                            <span class="fw-semibold ps-2 fs-6">
                                Male
                            </span>
                        </label>
                        <!--end::Option-->

                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                            <input class="form-check-input" name="gender"  type="radio" value="Female" required />
                            <span class="fw-semibold ps-2 fs-6">
                                Female
                            </span>
                        </label>
                        <!--end::Option-->
                    </div>
                    <!--end::Options-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Home Address 1</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="home_address" id="home_address" class="form-control form-control-lg form-control-solid" placeholder="Address"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Home Address 2</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="home_address2" id="home_address2" class="form-control form-control-lg form-control-solid" placeholder="Address"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Date of Birth</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="date" name="dateofbirth" id="dateofbirth" onkeyup="showage()" class="form-control form-control-lg form-control-solid" placeholder="Date of Birth"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->



               <!--begin::Input group-->
               <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Age</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="age1" id="age1"  onkeyup="showage()" class="form-control form-control-lg form-control-solid" placeholder="Age"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

             <!--begin::Input group-->
             <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Place of Birth</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="placeofbirth" id="placeofbirth" class="form-control form-control-lg form-control-solid" placeholder="Birth Place"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->
                 <script type="text/javascript">

                            var nigeria = new Array(
                                "Abia State",
                                "Adamawa State",
                                "Akwa Ibom State",
                                "Anambra State",
                                "Bauchi State",
                                "Bayelsa State",
                                "Benue State",
                                "Borno State",
                                "Cross River State",
                                "Delta State",
                                "Ebonyi State",
                                "Edo State",
                                "Ekiti",
                                "Enugu State",
                                "FCT",
                                "Gombe State",
                                "Imo State",
                                "Jigawa State",
                                "Kaduna State",
                                "Kano State",
                                "Katsina State",
                                "Kebbi State",
                                "Kogi State",
                                "Kwara State",
                                "Lagos State",
                                "Nasarawa State",
                                "Niger State",
                                "Ogun State",
                                "Ondo State",
                                "Osun State",
                                "Oyo State",
                                "Plateau State",
                                "Rivers State",
                                "Sokoto State",
                                "Taraba State",
                                "Yobe State",
                                "Zamfara State");



                            var s_a = new Array();

                            s_a[0] = "";
                            s_a[1] = "Aba South|Arochukwu|Bende|Ikwuano|Isiala Ngwa North|Isiala Ngwa South|Isuikwuato|Obi Ngwa|Ohafia|Osisioma|Ugwunagbo|Ukwa East|Ukwa West|Umuahia North|Umuahia South|Umu Nneochi";
                            s_a[2] = "Fufure|Ganye|Gayuk|Gombi|Grie|Hong|Jada|Lamurde|Madagali|Maiha|Mayo Belwa|Michika|Mubi North|Mubi South|Numan|Shelleng|Song|Toungo|Yola North|Yola South";
                            s_a[3] = "Eastern Obolo|Eket|Esit Eket|Essien Udim|Etim Ekpo|Etinan|Ibeno|Ibesikpo Asutan|Ibiono-Ibom|Ika|Ikono|Ikot Abasi|Ikot Ekpene|Ini|Itu|Mbo|Mkpat-Enin|Nsit-Atai|Nsit-Ibom|Nsit-Ubium|Obot Akara|Okobo|Onna|Oron|Oruk Anam|Udung-Uko|Ukanafun|Uruan|Urue-Offong Oruko|Uyo";
                            s_a[4] = "Anambra East|Anambra West|Anaocha|Awka North|Awka South|Ayamelum|Dunukofia|Ekwusigo|Idemili North|Idemili South|Ihiala|Njikoka|Nnewi North|Nnewi South|Ogbaru|Onitsha North|Onitsha South|Orumba North|Orumba South|Oyi";
                            s_a[5] = "Bauchi|Bogoro|Damban|Darazo|Dass|Gamawa|Ganjuwa|Giade|Itas\/Gadau|Jama are|Katagum|Kirfi|Misau|Ningi|Shira|Tafawa Balewa|Toro|Warji|Zaki";
                            s_a[6] = "Ekeremor|Kolokuma\/Opokuma|Nembe|Ogbia|Sagbama|Southern Ijaw|Yenagoa";
                            s_a[7] = "Apa|Ado|Buruku|Gboko|Guma|Gwer East|Gwer West|Katsina-Ala|Konshisha|Kwande|Logo|Makurdi|Obi|Ogbadibo|Ohimini|Oju|Okpokwu|Oturkpo|Tarka|Ukum|Ushongo|Vandeikya";
                            s_a[8] = "Askira\/Uba|Bama|Bayo|Biu|Chibok|Damboa|Dikwa|Gubio|Guzamala|Gwoza|Hawul|Jere|Kaga|Kala\/Balge|Konduga|Kukawa|Kwaya Kusar|Mafa|Magumeri|Maiduguri|Marte|Mobbar|Monguno|Ngala|Nganzai|Shani";
                            s_a[9] = "Cross River State|Akamkpa|Akpabuyo|Bakassi|Bekwarra|Biase|Boki|Calabar Municipal|Calabar South|Etung|Ikom|Obanliku|Obubra|Obudu|Odukpani|Ogoja|Yakuur|Yala";
                            s_a[10] = "Aniocha Souths|Bomadi|Burutu|Ethiope East|Ethiope West|Ika North East|Ika South|Isoko North|Isoko South|Ndokwa East|Ndokwa West|Okpe|Oshimili North|Oshimili South|Patani|Sapele|Udu|Ughelli North|Ughelli South|Ukwuani|Uvwie|Warri North|Warri South|Warri South West";
                            s_a[11] = "Afikpo North|Afikpo South|Ebonyi|Ezza North|Ezza South|Ikwo|Ishielu|Ivo|Izzi|Ohaozara|Ohaukwu|Onicha";
                            s_a[12]= "Egor|Esan Central|Esan North-East|Esan South-East|Esan West|Etsako Central|Etsako East|Etsako West|Igueben|Ikpoba Okha|Orhionmwon|Oredo|Ovia North-East|Ovia South-West|Owan East|Owan West|Uhunmwonde";
                            s_a[13]= "Efon|Ekiti East|Ekiti South-West|Ekiti West|Emure|Gbonyin|Ido Osi|Ijero|Ikere|Ikole|Ilejemeje|Irepodun\/Ifelodun|Ise\/Orun|Moba|Oye";
                            s_a[14]="Awgu|Enugu East|Enugu North|Enugu South|Ezeagu|Igbo Etiti|Igbo Eze North|Igbo Eze South|Isi Uzo|Nkanu East|Nkanu West|Nsukka|Oji River|Udenu|Udi|Uzo Uwani";
                            s_a[15]= "Bwari|Gwagwalada|Kuje|Kwali|Municipal Area Council";
                            s_a[16]= "Balanga|Billiri|Dukku|Funakaye|Gombe|Kaltungo|Kwami|Nafada|Shongom|Yamaltu\/Deba";
                            s_a[17]= "Ahiazu Mbaise|Ehime Mbano|Ezinihitte|Ideato North|Ideato South|Ihitte\/Uboma|Ikeduru|Isiala Mbano|Isu|Mbaitoli|Ngor Okpala|Njaba|Nkwerre|Nwangele|Obowo|Oguta|Ohaji\/Egbema|Okigwe|Orlu|Orsu|Oru East|Oru West|Owerri Municipal|Owerri North|Owerri West|Unuimo";
                            s_a[18]= "Babura|Biriniwa|Birnin Kudu|Buji|Dutse|Gagarawa|Garki|Gumel|Guri|Gwaram|Gwiwa|Hadejia|Jahun|Kafin Hausa|Kazaure|Kiri Kasama|Kiyawa|Kaugama|Maigatari|Malam Madori|Miga|Ringim|Roni|Sule Tankarkar|Taura|Yankwashi";
                            s_a[19]= "Chikun|Giwa|Igabi|Ikara|Jaba|Jema'a|Kachia|Kaduna North|Kaduna South|Kagarko|Kajuru|Kaura|Kauru|Kubau|Kudan|Lere|Makarfi|Sabon Gari|Sanga|Soba|angon Kataf|Zaria";
                            s_a[20]= "Albasu|Bagwai|Bebeji|Bichi|Bunkure|Dala|Dambatta|Dawakin Kudu|Dawakin Tofa|Doguwa|Fagge|Gabasawa|Garko|Garun Mallam|Gaya|Gezawa|Gwale|Gwarzo|Kabo|Kano Municipal|Karaye|Kibiya|Kiru|Kumbotso|Kunchi|Kura|Madobi|Makoda|Minjibir|Nasarawa|Rano|Rimin Gado|Rogo|Shanono|Sumaila|Takai|Tarauni|Tofa|Tsanyawa|Tudun Wada|Ungogo|Warawa|Wudil";
                            s_a[21]= "Batagarawa|Batsari|Baure|Bindawa|Charanchi|Dandume|Danja|Dan Musa|Daura|Dutsi|Dutsin Ma|Faskari|Funtua|Ingawa|Jibia|Kafur|Kaita|Kankara|Kankia|Katsina|Kurfi|Kusada|Mai'Adua|Malumfashi|Mani|Mashi|Musawa|Rimi|Sabuwa|Safana|Sandamu|Zango";
                            s_a[22]= "Arewa Dandi|Argungu|Augie|Bagudo|Birnin Kebbi|Bunza|Dandi|Fakai|Gwandu|Jega|Kalgo|Koko\/Besse|Maiyama|Sakaba|Shanga|Suru|Wasagu\/Danko|Yauri|Zuru";
                            s_a[23]= "Ajaokuta|Ankpa|Bassa|Dekina|Ibaji|Idah|Igalamela Odolu|Ijumu|Kabba\/Bunu|Kogi|Lokoja|Mopa Muro|Ofu|Ogori\/Magongo|Okehi|Okene|Olamaboro|Omala|Yagba East|Yagba West";
                            s_a[24]= "Baruten|Edu|Ekiti|Ifelodun|Ilorin East|Ilorin South|Ilorin West|Irepodun|Isin|Kaiama|Moro|Offa|Oke Ero|Oyun|Pategi";
                            s_a[25]= "Ajeromi-Ifelodun|Alimosho|Amuwo-Odofin|Apapa|Badagry|Epe|Eti Osa|Ibeju-Lekki|Ifako-Ijaiye|Ikeja|Ikorodu|Kosofe|Lagos Island|Lagos Mainland|Mushin|Ojo|Oshodi-Isolo|Shomolu|Surulere";
                            s_a[26]= "Awe|Doma|Karu|Keana|Keffi|Kokona|Lafia|Nasarawa|Nasarawa Egon|Obi|Toto|Wamba";
                            s_a[27]= "Agwara|Bida|Borgu|Bosso|Chanchaga|Edati|Gbako|Gurara|Katcha|Kontagora|Lapai|Lavun|Magama|Mariga|Mashegu|Mokwa|Moya|Paikoro|Rafi|Rijau|Shiroro|Suleja|Tafa|Wushishi";
                            s_a[28]= "Abeokuta South|Ado-Odo\/Ota|Egbado North|Egbado South|Ewekoro|Ifo|Ijebu East|Ijebu North|Ijebu North East|Ijebu Ode|kenne|Imeko Afon|Ipokia|Obafemi Owode|Odeda|Odogbolu|Ogun Waterside|Remo North|Shagamu";
                            s_a[29]= "Akoko North-West|Akoko South-West|Akoko South-East|Akure North|Akure South|Ese Odo|Idanre|Ifedore|Ilaje|Ile Oluji\/Okeigbo|Irele|Odigbo|Okitipupa|Ondo East|Ondo West|Ose|Owo";
                            s_a[30]= "Atakunmosa West|Aiyedaade|Aiyedire|Boluwaduro|Boripe|Ede North|Ede South|Ife Central|Ife East|Ife North|Ife South|Egbedore|Ejigbo|Ifedayo|Ifelodun|Ila|Ilesa East|Ilesa West|Irepodun|Irewole|Isokan|Iwo|Obokun|Odo Otin|Ola Oluwa|Olorunda|Oriade|Orolu|Osogbo";
                            s_a[31]= "Akinyele|Atiba|Atisbo|Egbeda|Ibadan North|Ibadan North-East|Ibadan North-West|Ibadan South-East|Ibadan South-West|Ibarapa Central|Ibarapa East|Ibarapa North|Ido|Irepo|Iseyin|Itesiwaju|Iwajowa|Kajola|Lagelu|Ogbomosho North|Ogbomosho South|Ogo Oluwa|Olorunsogo|Oluyole|Ona Ara|Orelope|Ori Ire|Oyo|Oyo East|Saki East|Saki West|Surulere";
                            s_a[32]= "Barkin Ladi|Bassa|Jos East|os North|Jos South|Kanam|Kanke|Langtang South|Langtang North|Mangu|Mikang|Pankshin|Qua'an Pan|Riyom|Shendam|Wase";
                            s_a[33]= "Ahoada East|Ahoada West|Akuku-Toru|Andoni|Asari-Toru|Bonny|Degema|Eleme|Emuoha|Etche|Gokana|Ikwerre|Khana|Obio\/Akpor|Ogba\/Egbema\/Ndoni|Ogu\/Bolo|Okrika|Omuma|Opobo\/Nkoro|Oyigbo|Port Harcourt|Tai";
                            s_a[34]= "Bodinga|Dange Shuni|Gada|Goronyo|Gudu|Gwadabawa|Illela|Isa|Kebbe|Kware|Rabah|Sabon Birni|Shagari|Silame|Sokoto North|Sokoto South|Tambuwal|Tangaza|Tureta|Wamako|Wurno|Yabo";
                            s_a[35]= "Bali|Donga|Gashaka|Gassol|Ibi|Jalingo|Karim Lamido|Kumi|Lau|Sardauna|Takum|Ussa|Wukari|Yorro|Zing";
                            s_a[36]= "Bursari|Damaturu|Fika|Fune|Geidam|Gujba|Gulani|Jakusko|Karasuwa|Machina|Nangere|Nguru|Potiskum|Tarmuwa|Yunusari|Yusufari";
                            s_a[37]= "Bakura|Birnin Magaji\/Kiyaw|Bukkuyum|Bungudu|Gummi|Gusau|Kaura Namoda|Maradun|Maru|Shinkafi|Talata Mafara|Chafe|Zurmi";

                            function populateStates(countryElementId, stateElementId) {

                                var selectedCountryIndex = document.getElementById(countryElementId).selectedIndex;

                                var stateElement = document.getElementById(stateElementId);

                                stateElement.length = 0; // Fixed by Julian Woods
                                stateElement.options[0] = new Option('Select Local Government', '');
                                stateElement.selectedIndex = 0;

                                var state_arr = s_a[selectedCountryIndex].split("|");

                                for (var i = 0; i < state_arr.length; i++) {
                                    stateElement.options[stateElement.length] = new Option(state_arr[i], state_arr[i]);
                                }
                            }

                            function populateCountries(countryElementId, stateElementId) {
                                // given the id of the <select> tag as function argument, it inserts <option> tags
                                var countryElement = document.getElementById(countryElementId);
                                countryElement.length = 0;
                                countryElement.options[0] = new Option('Select State', '-1');
                                countryElement.selectedIndex = 0;
                                for (var i = 0; i < nigeria.length; i++) {
                                    countryElement.options[countryElement.length] = new Option(nigeria[i], nigeria[i]);
                                }

                                // Assigned all countries. Now assign event listener for the states.

                                if (stateElementId) {
                                    countryElement.onchange = function () {
                                        populateStates(countryElementId, stateElementId);
                                    };
                                }
                            }



                        function age()
                        {
                                var byear = document.getElementById('dateofbirth').value;
                                var newbyear = byear.substring(0, byear.length - 6);
                                var by = parseInt(newbyear);
                                var currentYear= new Date().getFullYear();
                                var ncy = parseInt(currentYear);
                                var age = ncy - newbyear ;

                                if (age < 10 ) {
                                    alert("Sorry, this age cannot be considered for registration!");
                                    Swal.fire('Hi, Your age '+ age);

                                    event.preventDefault();
                                }else if( age > 45){
                                    alert("Ooops!, this age is considered too old for registration!");
                                    Swal.fire('Hi, Your age '+ age);
                                    event.preventDefault();

                                } else{
                                    return true;
                                }

                                //console.log(byear)
                                }

                        function showage()
                        {
                                    var byear = document.getElementById('dateofbirth').value;
                                    var newbyear = byear.substring(0, byear.length - 6);
                                    var by = parseInt(newbyear);
                                    var currentYear= new Date().getFullYear();
                                    var ncy = parseInt(currentYear);
                                    var age = ncy - newbyear ;

                                    if (age < 10 ) {
                                        //alert("Sorry, this age cannot be concidered!");
                                        document.getElementById("age1").style.color = "red";
                                        document.getElementById("age1").value = age + " ...This age cannot be considered for registration";
                                        //return false;
                                    }
                                    else if( age > 45){

                                        document.getElementById("age1").style.color = "blue";
                                        document.getElementById("age1").value = age + " ...This age is considered too old for registration";

                                    }else{
                                        //return true;
                                        document.getElementById("age1").style.color = "green";
                                        document.getElementById("age1").value = age;
                                    }


                        }

                 </script>

              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nationality</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="nationality" id="nationality" class="form-control form-control-lg form-control-solid" placeholder="Nationality"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <!--begin::Input group-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">State of Origin</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <select  class="form-control form-control-lg form-control-solid" id="state" data-rel="chosen" name="state">
                        <option value="" selected>Select State </option>
                    </select>

                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Local Goverment</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <select  class="form-control form-control-lg form-control-solid" id="local" data-rel="chosen" name="local">
                        <option value="" selected>Select State </option>
                    </select>
                    </select>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->

            <script language="javascript">
                populateCountries("state", "local");
            </script>

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Religion</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" id="religion" data-rel="chosen" name="religion">
                            <option value="" selected>Select Religion </option>
                            <option value="Christianity">Christianity</option>
                            <option value="Islam">Islam</option>
                            <option value="others">Others</option>
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Last School Attended</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text" name="last_school" id="last_school" class="form-control form-control-lg form-control-solid" placeholder="Last School Attended"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


              <!--begin::Input group-->
              <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Last Class</label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <input type="text"  name="last_class" id="last_class" class="form-control form-control-lg form-control-solid" placeholder="Last Class"  />
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->




        </div>
        <!--end::Card body-->

        {{-- <!--begin::Actions-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
            <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
        </div>
        <!--end::Actions-->
    </form>
    <!--end::Form--> --}}
</div>
<!--end::Content-->
</div>
<!--end::Basic info-->
<!--begin::Sign-in Method-->
<div class="card  mb-5 mb-xl-10"   >
<!--begin::Card header-->
<div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
    <div class="card-title m-0">
        <h3 class="fw-bold m-0">Class Information</h3>
    </div>
</div>
<!--end::Card header-->

<!--begin::Content-->
<div id="kt_account_settings_signin_method" class="collapse show">
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Email Address-->
        <div class="d-flex flex-wrap align-items-center">
            <!--begin::Label-->
            <div id="kt_signin_email">
                <div class="fs-6 fw-bold mb-1">Provide Class Information</div>
                {{-- <div class="fw-semibold text-gray-600">{{ $user->email }}</div> --}}
            </div>
            <!--end::Label-->

            <!--begin::Edit-->
            <div id="kt_signin_email_edit" class="flex-row-fluid d-none">
                <!--begin::Form-->

                    <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Class</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="schoolclassid" id="schoolclassid" required>
                            <option value="">Select Class</option>
                            @foreach ($schoolclass as $class => $name )
                            <option value="{{$name->id}}">{{ $name->schoolclass }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $name->arm}} </option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->


                 <!--begin::Input group-->
                 <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Term</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="termid" id="termid" required>
                            <option value="">Select Term </option>
                            @foreach ($schoolterm as $term => $name )
                                <option value="{{$name->id}}">{{ $name->term}}</option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->



                 <!--begin::Input group-->
                 <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-semibold fs-6">Select Current Session</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">

                        <select  class="form-control form-control-lg form-control-solid" name ="sessionid" id="sessionid" required>
                            <option value="">Select Session </option>
                            @foreach ($schoolsession as $schoolsession => $name )
                                <option value="{{$name->id}}">{{ $name->session}}</option>
                            @endforeach
                        </select>

                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->
                 <!--begin::Input group-->
             <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label required fw-semibold fs-6">
                   Student Status
                </label>
                <!--end::Label-->

                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <!--begin::Options-->
                    <div class="d-flex align-items-center mt-3">
                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                            <input class="form-check-input" name="statusId"  type="radio" value="1" required />
                            <span class="fw-semibold ps-2 fs-6">
                               Old student
                            </span>
                        </label>
                        <!--end::Option-->

                        <!--begin::Option-->
                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                            <input class="form-check-input" name="statusId"  type="radio" value="2" required />
                            <span class="fw-semibold ps-2 fs-6">
                               New Student
                            </span>
                        </label>
                        <!--end::Option-->
                    </div>
                    <!--end::Options-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Input group-->


                    <div class="d-flex">
                        <button type="reset" class="btn btn-light btn-active-light-primary me-2">Discard</button>
                        <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Record</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Edit-->

            <!--begin::Action-->
            <div id="kt_signin_email_button" class="ms-auto">
                <button class="btn btn-light btn-active-light-primary">Click Here</button>
            </div>
            <!--end::Action-->
        </div>
        <!--end::Email Address-->

        <!--begin::Separator-->
        <div class="separator separator-dashed my-6"></div>
        <!--end::Separator-->
{{--
        <!--begin::Password-->
        <div class="d-flex flex-wrap align-items-center mb-10">
            <!--begin::Label-->
            <div id="kt_signin_password">
                <div class="fs-6 fw-bold mb-1">Password</div>
                <div class="fw-semibold text-gray-600">************</div>
            </div>
            <!--end::Label-->

            <!--begin::Edit-->
            <div id="kt_signin_password_edit" class="flex-row-fluid d-none">
                <!--begin::Form-->
                <form id="kt_signin_change_password" class="form" novalidate="novalidate" method="POST">
                    @csrf
                    <input type="hidden" name="pid" id="pid" value="{{ Auth::user()->id}}">
                    <div class="row mb-1">
                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="currentpassword" class="form-label fs-6 fw-bold mb-3">Current Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                name="currentpassword" id="currentpassword"  />
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="newpassword" class="form-label fs-6 fw-bold mb-3">New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                 name="newpassword" id="newpassword" />
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="fv-row mb-0">
                                <label for="confirmpassword" class="form-label fs-6 fw-bold mb-3">Confirm New Password</label>
                                <input type="password" class="form-control form-control-lg form-control-solid "
                                 name="confirmpassword" id="confirmpassword" />
                            </div>
                        </div>
                    </div>

                    <div class="form-text mb-5">Password must be at least 8 character and contain symbols</div>

                    <div class="d-flex">
                        <button id="kt_password_submit" type="button" class="btn btn-primary me-2 px-6">Update Password</button>
                        <button id="kt_password_cancel" type="button" class="btn btn-color-gray-400 btn-active-light-primary px-6">Cancel</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
            <!--end::Edit-->

            <!--begin::Action-->
            <div id="kt_signin_password_button" class="ms-auto">
                <button class="btn btn-light btn-active-light-primary">Reset Password</button>
            </div>
            <!--end::Action-->
        </div>
        <!--end::Password--> --}}


{{-- <!--begin::Notice-->
<div class="notice d-flex bg-light-primary rounded border-primary border border-dashed  p-6">
        <!--begin::Icon-->
    <i class="ki-duotone ki-shield-tick fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>        <!--end::Icon-->

<!--begin::Wrapper-->
<div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                <!--begin::Content-->
        <div class="mb-3 mb-md-0 fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Secure Your Account</h4>

                                <div class="fs-6 text-gray-700 pe-7">Two-factor authentication adds an extra layer of security to your account. To log in, in addition you'll need to provide a 6 digit code</div>
                        </div>
        <!--end::Content-->

                <!--begin::Action-->
        <a href="#" class="btn btn-primary px-6 align-self-center text-nowrap"  data-bs-toggle="modal" data-bs-target="#kt_modal_two_factor_authentication" >
            Enable            </a>
        <!--end::Action-->
        </div>
<!--end::Wrapper-->
</div>
<!--end::Notice--> --}}
    </div>
    <!--end::Card body-->
</div>
<!--end::Content-->
</div>
<!--end::Sign-in Method-->



{{-- <!--begin::Deactivate Account-->
<div class="card  "   >

    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_deactivate" aria-expanded="true" aria-controls="kt_account_deactivate">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Deactivate Account</h3>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Content-->
    <div id="kt_account_settings_deactivate" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_deactivate_form" class="form">

            <!--begin::Card body-->
            <div class="card-body border-top p-9">

        <!--begin::Notice-->
        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                <i class="ki-duotone ki-information fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>        <!--end::Icon-->

            <!--begin::Wrapper-->
            <div class="d-flex flex-stack flex-grow-1 ">
                            <!--begin::Content-->
                    <div class=" fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">You Are Deactivating Your Account</h4>

                                            <div class="fs-6 text-gray-700 ">For extra security, this requires you to confirm your email or phone number when you reset yousignr password. <br/><a class="fw-bold" href="#">Learn more</a></div>
                                    </div>
                    <!--end::Content-->

                    </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Notice-->

                    <!--begin::Form input row-->
                    <div class="form-check form-check-solid fv-row">
                        <input name="deactivate" class="form-check-input" type="checkbox" value="" id="deactivate" />
                        <label class="form-check-label fw-semibold ps-2 fs-6" for="deactivate">I confirm my account deactivation</label>
                    </div>
                    <!--end::Form input row-->
                </div>
                <!--end::Card body-->

                <!--begin::Card footer-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button id="kt_account_deactivate_account_submit" type="submit" class="btn btn-danger fw-semibold">Deactivate Account</button>
                </div>
                <!--end::Card footer-->

            </form>
            <!--end::Form-->
        </div>
        <!--end::Content-->
</div>
<!--end::Deactivate Account--> --}}
</div>
    <!--end::Content container-->
</div>
<!--end::Content-->
            </div>
            <!--end::Content wrapper-->

@endsection

