@extends('layouts.master')

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Edit Student Management
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('student.index') }}" class="text-muted text-hover-primary">Student Management</a>
                        </li>
                        <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                        <li class="breadcrumb-item text-muted">Edit Student Record</li>
                    </ul>
                </div>

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

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container">
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('student.index') }}" class="btn btn-sm fw-bold btn-primary">
                        << Back
                    </a>
                </div>

                <div class="card mb-5 mb-xl-10">
                    <div class="card-header border-0">
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">Student Details</h3>
                        </div>
                    </div>

                    <div class="card-body border-top p-9">
                        <form id="student-edit-form" method="POST" action="{{ route('student.update', $student->sid) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Admission No -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Admission No</label>
                                <div class="col-lg-8">
                                    <input type="text" name="admissionNo" class="form-control form-control-lg form-control-solid" value="{{ $student->admissionNo }}" required />
                                </div>
                            </div>

                            <!-- Title -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Title</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" name="title" required>
                                        <option value="" {{ !$student->title ? 'selected' : '' }}>Select Title</option>
                                        <option value="Mr" {{ $student->title == 'Mr' ? 'selected' : '' }}>Mr</option>
                                        <option value="Mrs" {{ $student->title == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                        <option value="Miss" {{ $student->title == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Full Name</label>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <input type="text" name="firstname" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="First name" value="{{ $student->firstname }}" required />
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="lastname" class="form-control form-control-lg form-control-solid" placeholder="Last name" value="{{ $student->lastname }}" required />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Other Name -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Other Name</label>
                                <div class="col-lg-8">
                                    <input type="text" name="othername" class="form-control form-control-lg form-control-solid" placeholder="Other names" value="{{ $student->othername }}" />
                                </div>
                            </div>

                            <!-- Gender -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Gender</label>
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mt-3">
                                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                                            <input class="form-check-input" name="gender" type="radio" value="Male" {{ $student->gender == 'Male' ? 'checked' : '' }} required />
                                            <span class="fw-semibold ps-2 fs-6">Male</span>
                                        </label>
                                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                                            <input class="form-check-input" name="gender" type="radio" value="Female" {{ $student->gender == 'Female' ? 'checked' : '' }} required />
                                            <span class="fw-semibold ps-2 fs-6">Female</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Home Address 1 -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Home Address 1</label>
                                <div class="col-lg-8">
                                    <input type="text" name="home_address" class="form-control form-control-lg form-control-solid" placeholder="Address" value="{{ $student->homeadd }}" required />
                                </div>
                            </div>

                            <!-- Home Address 2 -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Home Address 2</label>
                                <div class="col-lg-8">
                                    <input type="text" name="home_address2" class="form-control form-control-lg form-control-solid" placeholder="Address" value="{{ $student->homeadd2 }}" />
                                </div>
                            </div>

                            <!-- Date of Birth -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Date of Birth</label>
                                <div class="col-lg-8">
                                    <input type="date" name="dateofbirth" id="dateofbirth" oninput="showage()" class="form-control form-control-lg form-control-solid" value="{{ $student->dob }}" required />
                                </div>
                            </div>

                            <!-- Age -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Age</label>
                                <div class="col-lg-8">
                                    <input type="text" name="age" id="age" class="form-control form-control-lg form-control-solid" value="{{ $student->age }}" required />
                                </div>
                            </div>

                            <!-- Place of Birth -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Place of Birth</label>
                                <div class="col-lg-8">
                                    <input type="text" name="placeofbirth" class="form-control form-control-lg form-control-solid" value="{{ $student->pob }}" required />
                                </div>
                            </div>

                            <!-- Nationality -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nationality</label>
                                <div class="col-lg-8">
                                    <input type="text" name="nationality" class="form-control form-control-lg form-control-solid" value="{{ $student->nationality }}" required />
                                </div>
                            </div>

                            <!-- State of Origin -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">State of Origin</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" id="state" name="state" onchange="populateStates('state', 'local')" required>
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Local Government -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Local Government</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" id="local" name="local" required>
                                        <option value="">Select Local Government</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Religion -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Religion</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" name="religion" required>
                                        <option value="">Select Religion</option>
                                        <option value="Christianity" {{ $student->religion == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                                        <option value="Islam" {{ $student->religion == 'Islam' ? 'selected' : '' }}>Islam</option>
                                        <option value="Others" {{ $student->religion == 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Last School Attended -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Last School Attended</label>
                                <div class="col-lg-8">
                                    <input type="text" name="last_school" class="form-control form-control-lg form-control-solid" value="{{ $student->lastsch }}" />
                                </div>
                            </div>

                            <!-- Last Class -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Last Class</label>
                                <div class="col-lg-8">
                                    <input type="text" name="last_class" class="form-control form-control-lg form-control-solid" value="{{ $student->lastclass }}" />
                                </div>
                            </div>

                            <!-- Class -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Class</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" name="schoolclassid" required>
                                        <option value="">Select Class</option>
                                        @foreach ($schoolclass as $class)
                                            <option value="{{ $class->id }}" {{ $student->sclassid == $class->id ? 'selected' : '' }}>
                                                {{ $class->schoolclass }} - {{ $class->arm }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Term -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Term</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" name="termid" required>
                                        <option value="">Select Term</option>
                                        @foreach ($schoolterm as $term)
                                            <option value="{{ $term->id }}" {{ $student->termid == $term->id ? 'selected' : '' }}>
                                                {{ $term->term }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Session -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Session</label>
                                <div class="col-lg-8">
                                    <select class="form-control form-control-lg form-control-solid" name="sessionid" required>
                                        <option value="">Select Session</option>
                                        @foreach ($schoolsession as $session)
                                            <option value="{{ $session->id }}" {{ $student->sessionid == $session->id ? 'selected' : '' }}>
                                                {{ $session->session }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Student Status -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Student Status</label>
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mt-3">
                                        <label class="form-check form-check-custom form-check-inline form-check-solid me-5">
                                            <input class="form-check-input" name="statusId" type="radio" value="1" {{ $student->statusId == 1 ? 'checked' : '' }} required />
                                            <span class="fw-semibold ps-2 fs-6">Old Student</span>
                                        </label>
                                        <label class="form-check form-check-custom form-check-inline form-check-solid">
                                            <input class="form-check-input" name="statusId" type="radio" value="2" {{ $student->statusId == 2 ? 'checked' : '' }} required />
                                            <span class="fw-semibold ps-2 fs-6">New Student</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Avatar -->
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-semibold fs-6">Avatar</label>
                                <div class="col-lg-8">
                                    <input type="file" name="avatar" class="form-control form-control-lg form-control-solid" accept=".png, .jpg, .jpeg" />
                                    <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                    @if ($student->picture)
                                        <img src="{{ asset('storage/' . $student->picture) }}" alt="Current Avatar" style="max-width: 100px; margin-top: 10px;">
                                    @endif
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <button type="submit" class="btn btn-primary" id="submit-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Age calculation
    function showage() {
        var byear = document.getElementById('dateofbirth').value;
        if (!byear) return;

        var newbyear = byear.substring(0, 4);
        var by = parseInt(newbyear);
        var currentYear = new Date().getFullYear();
        var age = currentYear - by;

        var ageField = document.getElementById("age");
        if (age < 10) {
            ageField.style.color = "red";
            ageField.value = age + " ...This age cannot be considered for registration";
        } else if (age > 45) {
            ageField.style.color = "blue";
            ageField.value = age + " ...This age is considered too old for registration";
        } else {
            ageField.style.color = "green";
            ageField.value = age;
        }
    }

    // State and Local Government population
    var nigeria = [
        "Abia State", "Adamawa State", "Akwa Ibom State", "Anambra State", "Bauchi State", "Bayelsa State", "Benue State", "Borno State", "Cross River State", "Delta State", "Ebonyi State", "Edo State", "Ekiti", "Enugu State", "FCT", "Gombe State", "Imo State", "Jigawa State", "Kaduna State", "Kano State", "Katsina State", "Kebbi State", "Kogi State", "Kwara State", "Lagos State", "Nasarawa State", "Niger State", "Ogun State", "Ondo State", "Osun State", "Oyo State", "Plateau State", "Rivers State", "Sokoto State", "Taraba State", "Yobe State", "Zamfara State"
    ];

    var s_a = [
        "",
        "Aba South|Arochukwu|Bende|Ikwuano|Isiala Ngwa North|Isiala Ngwa South|Isuikwuato|Obi Ngwa|Ohafia|Osisioma|Ugwunagbo|Ukwa East|Ukwa West|Umuahia North|Umuahia South|Umu Nneochi",
        "Fufure|Ganye|Gayuk|Gombi|Grie|Hong|Jada|Lamurde|Madagali|Maiha|Mayo Belwa|Michika|Mubi North|Mubi South|Numan|Shelleng|Song|Toungo|Yola North|Yola South",
        "Eastern Obolo|Eket|Esit Eket|Essien Udim|Etim Ekpo|Etinan|Ibeno|Ibesikpo Asutan|Ibiono-Ibom|Ika|Ikono|Ikot Abasi|Ikot Ekpene|Ini|Itu|Mbo|Mkpat-Enin|Nsit-Atai|Nsit-Ibom|Nsit-Ubium|Obot Akara|Okobo|Onna|Oron|Oruk Anam|Udung-Uko|Ukanafun|Uruan|Urue-Offong Oruko|Uyo",
        "Anambra East|Anambra West|Anaocha|Awka North|Awka South|Ayamelum|Dunukofia|Ekwusigo|Idemili North|Idemili South|Ihiala|Njikoka|Nnewi North|Nnewi South|Ogbaru|Onitsha North|Onitsha South|Orumba North|Orumba South|Oyi",
        "Bauchi|Bogoro|Damban|Darazo|Dass|Gamawa|Ganjuwa|Giade|Itas/Gadau|Jama are|Katagum|Kirfi|Misau|Ningi|Shira|Tafawa Balewa|Toro|Warji|Zaki",
        "Ekeremor|Kolokuma/Opokuma|Nembe|Ogbia|Sagbama|Southern Ijaw|Yenagoa",
        "Apa|Ado|Buruku|Gboko|Guma|Gwer East|Gwer West|Katsina-Ala|Konshisha|Kwande|Logo|Makurdi|Obi|Ogbadibo|Ohimini|Oju|Okpokwu|Oturkpo|Tarka|Ukum|Ushongo|Vandeikya",
        "Askira/Uba|Bama|Bayo|Biu|Chibok|Damboa|Dikwa|Gubio|Guzamala|Gwoza|Hawul|Jere|Kaga|Kala/Balge|Konduga|Kukawa|Kwaya Kusar|Mafa|Magumeri|Maiduguri|Marte|Mobbar|Monguno|Ngala|Nganzai|Shani",
        "Akamkpa|Akpabuyo|Bakassi|Bekwarra|Biase|Boki|Calabar Municipal|Calabar South|Etung|Ikom|Obanliku|Obubra|Obudu|Odukpani|Ogoja|Yakuur|Yala",
        "Aniocha South|Bomadi|Burutu|Ethiope East|Ethiope West|Ika North East|Ika South|Isoko North|Isoko South|Ndokwa East|Ndokwa West|Okpe|Oshimili North|Oshimili South|Patani|Sapele|Udu|Ughelli North|Ughelli South|Ukwuani|Uvwie|Warri North|Warri South|Warri South West",
        "Afikpo North|Afikpo South|Ebonyi|Ezza North|Ezza South|Ikwo|Ishielu|Ivo|Izzi|Ohaozara|Ohaukwu|Onicha",
        "Egor|Esan Central|Esan North-East|Esan South-East|Esan West|Etsako Central|Etsako East|Etsako West|Igueben|Ikpoba Okha|Orhionmwon|Oredo|Ovia North-East|Ovia South-West|Owan East|Owan West|Uhunmwonde",
        "Efon|Ekiti East|Ekiti South-West|Ekiti West|Emure|Gbonyin|Ido Osi|Ijero|Ikere|Ikole|Ilejemeje|Irepodun/Ifelodun|Ise/Orun|Moba|Oye",
        "Awgu|Enugu East|Enugu North|Enugu South|Ezeagu|Igbo Etiti|Igbo Eze North|Igbo Eze South|Isi Uzo|Nkanu East|Nkanu West|Nsukka|Oji River|Udenu|Udi|Uzo Uwani",
        "Bwari|Gwagwalada|Kuje|Kwali|Municipal Area Council",
        "Balanga|Billiri|Dukku|Funakaye|Gombe|Kaltungo|Kwami|Nafada|Shongom|Yamaltu/Deba",
        "Ahiazu Mbaise|Ehime Mbano|Ezinihitte|Ideato North|Ideato South|Ihitte/Uboma|Ikeduru|Isiala Mbano|Isu|Mbaitoli|Ngor Okpala|Njaba|Nkwerre|Nwangele|Obowo|Oguta|Ohaji/Egbema|Okigwe|Orlu|Orsu|Oru East|Oru West|Owerri Municipal|Owerri North|Owerri West|Unuimo",
        "Babura|Biriniwa|Birnin Kudu|Buji|Dutse|Gagarawa|Garki|Gumel|Guri|Gwaram|Gwiwa|Hadejia|Jahun|Kafin Hausa|Kazaure|Kiri Kasama|Kiyawa|Kaugama|Maigatari|Malam Madori|Miga|Ringim|Roni|Sule Tankarkar|Taura|Yankwashi",
        "Chikun|Giwa|Igabi|Ikara|Jaba|Jema'a|Kachia|Kaduna North|Kaduna South|Kagarko|Kajuru|Kaura|Kauru|Kubau|Kudan|Lere|Makarfi|Sabon Gari|Sanga|Soba|Zangon Kataf|Zaria",
        "Albasu|Bagwai|Bebeji|Bichi|Bunkure|Dala|Dambatta|Dawakin Kudu|Dawakin Tofa|Doguwa|Fagge|Gabasawa|Garko|Garun Mallam|Gaya|Gezawa|Gwale|Gwarzo|Kabo|Kano Municipal|Karaye|Kibiya|Kiru|Kumbotso|Kunchi|Kura|Madobi|Makoda|Minjibir|Nasarawa|Rano|Rimin Gado|Rogo|Shanono|Sumaila|Takai|Tarauni|Tofa|Tsanyawa|Tudun Wada|Ungogo|Warawa|Wudil",
        "Batagarawa|Batsari|Baure|Bindawa|Charanchi|Dandume|Danja|Dan Musa|Daura|Dutsi|Dutsin Ma|Faskari|Funtua|Ingawa|Jibia|Kafur|Kaita|Kankara|Kankia|Katsina|Kurfi|Kusada|Mai'Adua|Malumfashi|Mani|Mashi|Musawa|Rimi|Sabuwa|Safana|Sandamu|Zango",
        "Arewa Dandi|Argungu|Augie|Bagudo|Birnin Kebbi|Bunza|Dandi|Fakai|Gwandu|Jega|Kalgo|Koko/Besse|Maiyama|Sakaba|Shanga|Suru|Wasagu/Danko|Yauri|Zuru",
        "Ajaokuta|Ankpa|Bassa|Dekina|Ibaji|Idah|Igalamela Odolu|Ijumu|Kabba/Bunu|Kogi|Lokoja|Mopa Muro|Ofu|Ogori/Magongo|Okehi|Okene|Olamaboro|Omala|Yagba East|Yagba West",
        "Baruten|Edu|Ekiti|Ifelodun|Ilorin East|Ilorin South|Ilorin West|Irepodun|Isin|Kaiama|Moro|Offa|Oke Ero|Oyun|Pategi",
        "Ajeromi-Ifelodun|Alimosho|Amuwo-Odofin|Apapa|Badagry|Epe|Eti Osa|Ibeju-Lekki|Ifako-Ijaiye|Ikeja|Ikorodu|Kosofe|Lagos Island|Lagos Mainland|Mushin|Ojo|Oshodi-Isolo|Shomolu|Surulere",
        "Awe|Doma|Karu|Keana|Keffi|Kokona|Lafia|Nasarawa|Nasarawa Egon|Obi|Toto|Wamba",
        "Agwara|Bida|Borgu|Bosso|Chanchaga|Edati|Gbako|Gurara|Katcha|Kontagora|Lapai|Lavun|Magama|Mariga|Mashegu|Mokwa|Moya|Paikoro|Rafi|Rijau|Shiroro|Suleja|Tafa|Wushishi",
        "Abeokuta South|Ado-Odo/Ota|Egbado North|Egbado South|Ewekoro|Ifo|Ijebu East|Ijebu North|Ijebu North East|Ijebu Ode|Ikenne|Imeko Afon|Ipokia|Obafemi Owode|Odeda|Odogbolu|Ogun Waterside|Remo North|Shagamu",
        "Akoko North-West|Akoko South-West|Akoko South-East|Akure North|Akure South|Ese Odo|Idanre|Ifedore|Ilaje|Ile Oluji/Okeigbo|Irele|Odigbo|Okitipupa|Ondo East|Ondo West|Ose|Owo",
        "Atakunmosa West|Aiyedaade|Aiyedire|Boluwaduro|Boripe|Ede North|Ede South|Ife Central|Ife East|Ife North|Ife South|Egbedore|Ejigbo|Ifedayo|Ifelodun|Ila|Ilesa East|Ilesa West|Irepodun|Irewole|Isokan|Iwo|Obokun|Odo Otin|Ola Oluwa|Olorunda|Oriade|Orolu|Osogbo",
        "Akinyele|Atiba|Atisbo|Egbeda|Ibadan North|Ibadan North-East|Ibadan North-West|Ibadan South-East|Ibadan South-West|Ibarapa Central|Ibarapa East|Ibarapa North|Ido|Irepo|Iseyin|Itesiwaju|Iwajowa|Kajola|Lagelu|Ogbomosho North|Ogbomosho South|Ogo Oluwa|Olorunsogo|Oluyole|Ona Ara|Orelope|Ori Ire|Oyo|Oyo East|Saki East|Saki West|Surulere",
        "Barkin Ladi|Bassa|Jos East|Jos North|Jos South|Kanam|Kanke|Langtang South|Langtang North|Mangu|Mikang|Pankshin|Qua'an Pan|Riyom|Shendam|Wase",
        "Ahoada East|Ahoada West|Akuku-Toru|Andoni|Asari-Toru|Bonny|Degema|Eleme|Emuoha|Etche|Gokana|Ikwerre|Khana|Obio/Akpor|Ogba/Egbema/Ndoni|Ogu/Bolo|Okrika|Omuma|Opobo/Nkoro|Oyigbo|Port Harcourt|Tai",
        "Bodinga|Dange Shuni|Gada|Goronyo|Gudu|Gwadabawa|Illela|Isa|Kebbe|Kware|Rabah|Sabon Birni|Shagari|Silame|Sokoto North|Sokoto South|Tambuwal|Tangaza|Tureta|Wamako|Wurno|Yabo",
        "Bali|Donga|Gashaka|Gassol|Ibi|Jalingo|Karim Lamido|Kumi|Lau|Sardauna|Takum|Ussa|Wukari|Yorro|Zing",
        "Bursari|Damaturu|Fika|Fune|Geidam|Gujba|Gulani|Jakusko|Karasuwa|Machina|Nangere|Nguru|Potiskum|Tarmuwa|Yunusari|Yusufari",
        "Bakura|Birnin Magaji/Kiyaw|Bukkuyum|Bungudu|Gummi|Gusau|Kaura Namoda|Maradun|Maru|Shinkafi|Talata Mafara|Chafe|Zurmi"
    ];

    function populateStates(countryElementId, stateElementId) {
        var selectedCountryIndex = document.getElementById(countryElementId).selectedIndex;
        var stateElement = document.getElementById(stateElementId);
        stateElement.length = 0;
        stateElement.options[0] = new Option('Select Local Government', '');
        stateElement.selectedIndex = 0;

        if (s_a[selectedCountryIndex]) {
            var state_arr = s_a[selectedCountryIndex].split("|");
            for (var i = 0; i < state_arr.length; i++) {
                stateElement.options[stateElement.length] = new Option(state_arr[i], state_arr[i]);
            }
        }
    }

    function populateCountries(countryElementId, stateElementId) {
        var countryElement = document.getElementById(countryElementId);
        countryElement.length = 0;
        countryElement.options[0] = new Option('Select State', '');
        countryElement.selectedIndex = 0;
        for (var i = 0; i < nigeria.length; i++) {
            countryElement.options[countryElement.length] = new Option(nigeria[i], nigeria[i]);
        }

        if (stateElementId) {
            countryElement.onchange = function () {
                populateStates(countryElementId, stateElementId);
            };
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        populateCountries('state', 'local');

        var selectedState = "{{ $student->state }}";
        if (selectedState) {
            var stateDropdown = document.getElementById('state');
            for (var i = 0; i < stateDropdown.options.length; i++) {
                if (stateDropdown.options[i].value === selectedState) {
                    stateDropdown.selectedIndex = i;
                    populateStates('state', 'local');
                    break;
                }
            }
        }

        var selectedLocalGovernment = "{{ $student->local }}";
        if (selectedLocalGovernment) {
            setTimeout(() => {
                var localDropdown = document.getElementById('local');
                for (var i = 0; i < localDropdown.options.length; i++) {
                    if (localDropdown.options[i].value === selectedLocalGovernment) {
                        localDropdown.selectedIndex = i;
                        break;
                    }
                }
            }, 100);
        }

        // Debug form submission
        document.getElementById('student-edit-form').addEventListener('submit', function (e) {
            console.log('Form submission triggered');
        });
    });
</script>
@endsection
