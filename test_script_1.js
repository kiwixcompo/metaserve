
    const statesData = {
        "Abia": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala-Ngwa North", "Isiala-Ngwa South", "Isuikwato", "Obi Nwa", "Ohafia", "Osisioma", "Ngwa", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu-Neochi"],
        "Adamawa": ["Demsa", "Fufore", "Ganaye", "Gireri", "Gombi", "Guyuk", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo-Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"],
        "Anambra": ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili south", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"],
        "Akwa Ibom": ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat Enin", "Nsit Atai", "Nsit Ibom", "Nsit Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"],
        "Bauchi": ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa-Balewa", "Toro", "Warji", "Zaki"],
        "Bayelsa": ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Jaw", "Yenegoa"],
        "Benue": ["Ado", "Agatu", "Apa", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Oju", "Okpokwu", "Ohimini", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"],
        "Borno": ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"],
        "Cross River": ["Akpabuyo", "Odukpani", "Akamkpa", "Biase", "Abi", "Ikom", "Yarkur", "Odubra", "Boki", "Ogoja", "Yala", "Obanliku", "Obudu", "Calabar South", "Etung", "Bekwara", "Bakassi", "Calabar Municipality"],
        "Delta": ["Oshimili", "Aniocha", "Aniocha South", "Ika South", "Ika North-East", "Ndokwa West", "Ndokwa East", "Isoko south", "Isoko North", "Bomadi", "Burutu", "Ughelli South", "Ughelli North", "Ethiope West", "Ethiope East", "Sapele", "Okpe", "Warri North", "Warri South", "Uvwie", "Udu", "Warri Central", "Ukwani", "Oshimili North", "Patani"],
        "Ebonyi": ["Edda", "Afikpo", "Onicha", "Ohaozara", "Abakaliki", "Ishielu", "lkwo", "Ezza", "Ezza South", "Ohaukwu", "Ebonyi", "Ivo"],
        "Enugu": ["Enugu South", "Igbo-Eze South", "Enugu North", "Nkanu", "Udi Agwu", "Oji-River", "Ezeagu", "IgboEze North", "Isi-Uzo", "Nsukka", "Igbo-Ekiti", "Uzo-Uwani", "Enugu Eas", "Aninri", "Nkanu East", "Udenu"],
        "Edo": ["Esan North-East", "Esan Central", "Esan West", "Egor", "Ukpoba", "Central", "Etsako Central", "Igueben", "Oredo", "Ovia SouthWest", "Ovia South-East", "Orhionwon", "Uhunmwonde", "Etsako East", "Esan South-East"],
        "Ekiti": ["Ado", "Ekiti-East", "Ekiti-West", "Emure/Ise/Orun", "Ekiti South-West", "Ikere", "Irepodun", "Ijero", "Ido/Osi", "Oye", "Ikole", "Moba", "Gbonyin", "Efon", "Ise/Orun", "Ilejemeje"],
        "FCT": ["Abaji", "Abuja Municipal", "Bwari", "Gwagwalada", "Kuje", "Kwali"],
        "Gombe": ["Akko", "Balanga", "Billiri", "Dukku", "Kaltungo", "Kwami", "Shomgom", "Funakaye", "Gombe", "Nafada/Bajoga", "Yamaltu/Delta"],
        "Imo": ["Aboh-Mbaise", "Ahiazu-Mbaise", "Ehime-Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor-Okpala", "Njaba", "Nwangele", "Nkwerre", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri-Municipal", "Owerri North", "Owerri West"],
        "Jigawa": ["Auyo", "Babura", "Birni Kudu", "Biriniwa", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafin Hausa", "Kaugama Kazaure", "Kiri Kasamma", "Kiyawa", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule-Tankarkar", "Taura", "Yankwashi"],
        "Kaduna": ["Birni-Gwari", "Chikun", "Giwa", "Igabi", "Ikara", "jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kajuru", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon-Gari", "Sanga", "Soba", "Zango-Kataf", "Zaria"],
        "Kano": ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garum", "Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Kano Municipal", "Karaye", "Kibiya", "Kiru", "kumbotso", "Ghari", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takali", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"],
        "Katsina": ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dandume", "Danja", "Dan Musa", "Daura", "Dutsi", "Dutsin-Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Matazuu", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"],
        "Kebbi": ["Aleiro", "Arewa-Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Wasagu/Danko", "Yauri", "Zuru"],
        "Kogi": ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela-Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa-Muro", "Ofu", "Ogori/Mangongo", "Okehi", "Okene", "Olamabolo", "Omala", "Yagba East", "Yagba West"],
        "Kwara": ["Asa", "Baruten", "Edu", "Ekiti", "Ifelodun", "Ilorin East", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke-Ero", "Oyun", "Pategi"],
        "Lagos": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti-Osa", "Ibeju/Lekki", "Ifako-Ijaye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"],
        "Nasarawa": ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa-Eggon", "Obi", "Toto", "Wamba"],
        "Niger": ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Muya", "Pailoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"],
        "Ogun": ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Yewa North", "Yewa South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko-Afon", "Ipokia", "Obafemi-Owode", "Ogun Waterside", "Odeda", "Odogbolu", "Remo North", "Shagamu"],
        "Ondo": ["Akoko North East", "Akoko North West", "Akoko South Akure East", "Akoko South West", "Akure North", "Akure South", "Ese-Odo", "Idanre", "Ifedore", "Ilaje", "Ile-Oluji", "Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"],
        "Osun": ["Aiyedade", "Aiyedire", "Atakumosa East", "Atakumosa West", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Egbedore", "Ejigbo", "Ife Central", "Ife East", "Ife North", "Ife South", "Ifedayo", "Ifelodun", "Ila", "Ilesha East", "Ilesha West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo-Otin", "Ola-Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"],
        "Oyo": ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan Central", "Ibadan North", "Ibadan North West", "Ibadan South East", "Ibadan South West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona-Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"],
        "Plateau": ["Barikin Ladi", "Bassa", "Bokkos", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Shendam", "Wase"],
        "Rivers": ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Emohua", "Eleme", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omumma", "Opobo/Nkoro", "Oyigbo", "Port-Harcourt", "Tai"],
        "Sokoto": ["Binji", "Bodinga", "Dange-shnsi", "Gada", "Goronyo", "Gudu", "Gawabawa", "Illela", "Isa", "Kware", "kebbe", "Rabah", "Sabon birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tqngaza", "Tureta", "Wamako", "Wurno", "Yabo"],
        "Taraba": ["Ardo-kola", "Bali", "Donga", "Gashaka", "Cassol", "Ibi", "Jalingo", "Karin-Lamido", "Kurmi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"],
        "Yobe": ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Karawa", "Machina", "Nangere", "Nguru Potiskum", "Tarmua", "Yunusari", "Yusufari"],
        "Zamfara": ["Anka", "Bakura", "Birnin Magaji", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura", "Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Tsafe", "Zurmi"]
    };

    function toggleCategoryFields() {
        try {
            const typeElem = document.querySelector('input[name="type"]:checked');
            if(!typeElem) return;
            
            document.getElementById('fullFormContainer').style.display = 'block';
        
        const type = typeElem.value;
        const tsuFields = document.getElementById('tsuFields');
        const extFields = document.getElementById('extFields');
        
        const regInput = document.getElementById('reg_number');
        const deptInput = document.getElementById('department_id');
        const tsuLevels = document.querySelectorAll('.tsu-level');
        const hqInput = document.getElementById('highest_qualification');
        const occInput = document.getElementById('occupation');

        const progSelect = document.getElementById('programme_id');

        if (type === 'tsu_student') {
            tsuFields.style.display = 'flex';
            extFields.style.display = 'none';
            regInput.required = true;
            deptInput.required = true;
            tsuLevels[0].required = true;
            hqInput.required = false;
            occInput.required = false;
        } else {
            tsuFields.style.display = 'none';
            extFields.style.display = 'flex';
            regInput.required = false;
            deptInput.required = false;
            tsuLevels[0].required = false;
            hqInput.required = true;
            occInput.required = true;
        }
        } catch (e) {
            alert('JavaScript Error: ' + e.message);
        }
    }

    function toggleNigerianFields() {
        const nationality = document.getElementById('nationality').value;
        const nigerianFields = document.querySelectorAll('.nigerian-only');
        const stateSelect = document.getElementById('state_of_origin');
        const lgaSelect = document.getElementById('lga');
        
        if (nationality === 'Nigerian') {
            nigerianFields.forEach(el => el.style.display = 'block');
            stateSelect.required = true;
            lgaSelect.required = true;
            
            // Populate states if empty
            if(stateSelect.options.length <= 1) {
                for (const state in statesData) {
                    const option = document.createElement('option');
                    option.value = state;
                    option.text = state;
                    stateSelect.add(option);
                }
            }
        } else {
            nigerianFields.forEach(el => el.style.display = 'none');
            stateSelect.required = false;
            lgaSelect.required = false;
        }
    }

    function populateLGA() {
        const state = document.getElementById('state_of_origin').value;
        const lgaSelect = document.getElementById('lga');
        
        // Clear previous options
        lgaSelect.innerHTML = '<option value="">Select LGA...</option>';
        
        if (state && statesData[state]) {
            statesData[state].forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.text = lga;
                lgaSelect.add(option);
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Limit course area checkboxes to 3
        const cbs = document.querySelectorAll('.course-area-cb');
        cbs.forEach(cb => {
            cb.addEventListener('change', function() {
                const checked = document.querySelectorAll('.course-area-cb:checked').length;
                if(checked >= 3) {
                    cbs.forEach(c => { if(!c.checked) c.disabled = true; });
                } else {
                    cbs.forEach(c => { if(c.id !== 'area_other' || (c.id === 'area_other' && c.checked)) c.disabled = false; else if(c.id === 'area_other') c.disabled = false; });
                }
            });
        });

        const otherCb = document.getElementById('area_other');
        const otherText = document.getElementById('course_area_other');
        otherCb.addEventListener('change', function() {
            otherText.disabled = !this.checked;
            if(this.checked) otherText.focus();
            else otherText.value = '';
        });
    });

    function nextStep(step) {
        if(step === 2) {
            let pass = document.getElementById('password').value;
            let conf = document.getElementById('confirm_password').value;
            let nationality = document.getElementById('nationality').value;
            let formValid = true;
            
            ['full_name', 'dob', 'nationality', 'phone', 'email'].forEach(f => {
                if(!document.querySelector(`[name="${f}"]`).value) formValid = false;
            });
            
            if (nationality === 'Nigerian') {
                if(!document.getElementById('state_of_origin').value) formValid = false;
                if(!document.getElementById('lga').value) formValid = false;
            }
            
            if(!document.querySelector(`input[name="gender"]:checked`)) formValid = false;

            if(!formValid || !pass) {
                alert('Please fill in all required fields in Step 1.');
                return;
            }
            if (pass !== conf) {
                alert('Passwords do not match!');
                return;
            }
        }
        
        if(step === 3) {
            const type = document.querySelector('input[name="type"]:checked').value;
            if(type === 'tsu_student') {
                if(!document.getElementById('reg_number').value || !document.getElementById('department_id').value || !document.querySelector('input[name="level"]:checked')) {
                    alert('Please complete all TSU Student fields.');
                    return;
                }
            } else {
                if(!document.getElementById('highest_qualification').value || !document.getElementById('occupation').value) {
                    alert('Please complete all External Candidate fields.');
                    return;
                }
            }
        }

        if(step === 4) {
            let progId = document.getElementById('programme_id').value;
            let courseValid = false;
            if (progId == 1 && document.getElementById('course_id_mandatory').value) courseValid = true;
            if (progId == 2 && document.getElementById('course_id_professional').value) courseValid = true;
            
            if(!courseValid || !document.getElementById('faculty_interest').value) {
                alert('Please select a course and fill in your Faculty / Field of Interest.');
                return;
            }
        }

        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-indicator').forEach(el => el.classList.remove('step-active'));
        
        document.getElementById(`step-${step}`).classList.add('active');
        document.getElementById(`ind-${step}`).classList.add('step-active');
        for(let i=1; i<step; i++) {
            document.getElementById(`ind-${i}`).style.background = 'var(--primary-color)';
            document.getElementById(`ind-${i}`).style.color = 'white';
        }
    }

    function goToStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-indicator').forEach(el => el.classList.remove('step-active'));
        
        document.getElementById(`step-${step}`).classList.add('active');
        document.getElementById(`ind-${step}`).classList.add('step-active');
        
        // Color completed steps indicator
        for(let i=1; i<=4; i++) {
            if(i < step) {
                document.getElementById(`ind-${i}`).style.background = 'var(--primary-color)';
                document.getElementById(`ind-${i}`).style.color = 'white';
            } else if (i > step) {
                document.getElementById(`ind-${i}`).style.background = '#e5e7eb';
                document.getElementById(`ind-${i}`).style.color = '#6b7280';
            }
        }
    }

    function prevStep(step) {
        goToStep(step);
    }

    function submitForm() {
        // Full Validation before submit
        
        // Step 1 Check
        let pass = document.getElementById('password').value;
        let conf = document.getElementById('confirm_password').value;
        let nationality = document.getElementById('nationality').value;
        let step1Valid = true;
        ['full_name', 'dob', 'nationality', 'phone', 'email'].forEach(f => {
            if(!document.querySelector(`[name="${f}"]`).value) step1Valid = false;
        });
        if (nationality === 'Nigerian') {
            if(!document.getElementById('state_of_origin').value) step1Valid = false;
            if(!document.getElementById('lga').value) step1Valid = false;
        }
        if(!document.querySelector(`input[name="gender"]:checked`)) step1Valid = false;
        if(!step1Valid || !pass || pass !== conf) {
            alert('Please fill in all required fields in Step 1 properly (Passwords must match).');
            goToStep(1);
            return;
        }

        // Step 2 Check
        const typeElem = document.querySelector('input[name="type"]:checked');
        if(!typeElem) { alert('Please select Applicant Category.'); return; }
        const type = typeElem.value;
        if(type === 'tsu_student') {
            if(!document.getElementById('reg_number').value || !document.getElementById('department_id').value || !document.querySelector('input[name="level"]:checked')) {
                alert('Please complete all TSU Student fields in Step 2.');
                goToStep(2);
                return;
            }
        } else {
            if(!document.getElementById('highest_qualification').value || !document.getElementById('occupation').value) {
                alert('Please complete all External Candidate fields in Step 2.');
                goToStep(2);
                return;
            }
        }

        // Step 3 Check
        let progId = document.getElementById('programme_id').value;
        let courseValid = false;
        if (progId == 1 && document.getElementById('course_id_mandatory').value) courseValid = true;
        if (progId == 2 && document.getElementById('course_id_professional').value) courseValid = true;
        
        if(!courseValid || !document.getElementById('faculty_interest').value) {
            alert('Please select a course and fill in your Faculty / Field of Interest in Step 3.');
            goToStep(3);
            return;
        }

        // Step 4 Check
        if(!document.querySelector('input[name="how_did_you_hear"]:checked')) {
            alert('Please select how you heard about us in Step 4.');
            goToStep(4);
            return;
        }
        if(!document.getElementById('why_join').value) {
            alert('Please tell us why you want to join in Step 4.');
            goToStep(4);
            return;
        }
        if(!document.getElementById('declaration').checked) {
            alert('You must accept the declaration in Step 4 to proceed.');
            goToStep(4);
            return;
        }

        // Set the actual course_id based on selected programme tab
        let selectedCourse = (progId == 1) ? document.getElementById('course_id_mandatory').value : document.getElementById('course_id_professional').value;
        
        let courseInput = document.getElementById('final_course_id');
        if(!courseInput) {
            courseInput = document.createElement('input');
            courseInput.type = 'hidden';
            courseInput.name = 'course_id';
            courseInput.id = 'final_course_id';
            document.getElementById('registerForm').appendChild(courseInput);
        }
        courseInput.value = selectedCourse;

        // All good, submit!
        document.getElementById('registerForm').submit();
    }
