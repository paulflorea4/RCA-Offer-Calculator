<div class="rca-wrapper">
<form class="rca-form" action="{{ route('rca.offer') }}" method="POST">
    @csrf

    <h1>Calculator RCA</h1>

    <h2>Owner details</h2>

    <div class="section">
        <div class="radio-group">
        <label>Choose the type of person</label><br>

        <input type="radio" name="Person[type]" id="person_individual" value="individual" checked>
        <label for="person_individual">Individual</label>

        <input type="radio" name="Person[type]" id="person_company" value="company">
        <label for="person_company">Legal Entity</label>
        </div>
    </div>

    <div id="individualFields">
        <div class="field">
            <label >First Name *</label>
            <input type="text" name="Person[firstName]" required>
        </div>

        <div class="field">
            <label>Last Name *</label>
            <input type="text" name="Person[lastName]" required>
        </div>

        <div class="field">
            <label>CNP *</label>
            <input type="text" name="Person[individualTaxId]" required>
        </div>

        <div class="field">
            <label>Driving license</label>
            <input type="date" name="Person[drivingLicense][issueDate]" required>
        </div>

        <div class="field">
            <label>Identity document *</label>
            <select name="Person[identification][idType]" required>
                <option value="CI" selected>BI/CI</option>
                <option value="PASSPORT">Pasaport</option>
            </select>
        </div>

        <div class="field">
            <label>ID Number</label>
            <input type="text" name="Person[identification][idNumber]" required>
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="Person[email]">
        </div>

        <div class="field">
            <label>Mobile Number</label>
            <input type="tel" name="Person[mobileNumber]">
        </div>


    <h2>Address</h2>
    <div class="section">
    @if(isset($error))
        <div style="color:red;">Error: {{ $error }}</div>
    @endif

    <div class="field">
    <label>County *</label>
    <select id="countySelect" required>
        <option value="">Select County</option>
        @foreach($counties as $county)
            <option
                value="{{ $county['id'] }}"
                data-code="{{ $county['code'] }}">
                {{ $county['name'] }}
            </option>
        @endforeach
    </select>
    </div>

    <div class="field">
    <label>City *</label>
    <select id="localitySelect" required>
        <option value="">Select City</option>
    </select>
    </div>

    <input type="hidden" name="Person[address][countyId]" id="hiddenCountyId">
    <input type="hidden" name="Person[address][countyCode]" id="hiddenCountyCode">
    <input type="hidden" name="Person[address][localityName]" id="hiddenLocalityName">
    <input type="hidden" name="Person[address][localitySiruta]" id="hiddenLocalitySiruta">

    <div class="field">
        <label>Street *</label>
        <input type="text" name="Person[address][street]" required>
    </div>

    <div class="field">
        <label>House Number</label>
        <input type="text" name="Person[address][houseNumber]">
    </div>

    <div class="field">
        <label>Building</label>
        <input type="text" name="Person[address][building]">
    </div>

    <div class="field">
        <label>Staircase</label>
        <input type="text" name="Person[address][staircase]">
    </div>

    <div class="field">
        <label>Floor</label>
        <input type="text" name="Person[address][floor]">
    </div>

    <div class="field">
        <label>Apartment</label>
        <input type="text" name="Person[address][apartment]">
    </div>

    <div class="field">
        <label>Postcode</label>
        <input type="text" name="Person[address][postcode]">
    </div>
    </div>
    </div>
    <div id="companyFields" style="display:none;">
        <div class="field">
            <label>Business Name *</label>
            <input type="text" name="Person[businessName]">
        </div>

        <div class="field">
            <label>CUI *</label>
            <input type="text" name="Person[companyTaxId]">
        </div>

        <h2>Sofer principal</h2>

        <div class="field">
            <label>Prenume *</label>
            <input type="text" name="Person[driverFirstName]">
        </div>

        <div class="field">
            <label>Nume *</label>
            <input type="text" name="Person[driverLastName]">
        </div>

        <div class="field">
            <label>CNP *</label>
            <input type="text" name="Person[driverTaxId]">
        </div>

        <div class="field">
            <label>Serie/Numar CI *</label>
            <input type="text" name="Person[driverIdentification][idNumber]">
        </div>

        <div class="field">
            <label>Numar de telefon</label>
            <input type="tel" name="Person[mobileNumber]">
        </div>
    </div>

    <h2>Vehicle data</h2>
    <div class="section">
    <div class="field">
        <label>License Plate *</label>
        <input type="text" name="Vehicle[licensePlate]" required>
    </div>

    <div class="field">
        <label>VIN *</label>
        <input type="text" name="Vehicle[vin]" required>
    </div>
    </div>

    <div style="text-align:right;margin-top:30px;">
        <button type="submit" class="submit-btn">
            Vezi oferte RCA
        </button>
    </div>
</form>
</div>

<script>
    const individualRadio = document.getElementById('person_individual');
    const companyRadio = document.getElementById('person_company');
    const individualFields = document.getElementById('individualFields');
    const companyFields = document.getElementById('companyFields');

    function togglePersonType() {
        if (individualRadio.checked) {
            individualFields.style.display = 'block';
            companyFields.style.display = 'none';

            individualFields.querySelector('input[name="Person[firstName]"]').required = true;
            individualFields.querySelector('input[name="Person[lastName]"]').required = true;
            individualFields.querySelector('input[name="Person[individualTaxId]"]').required = true;

            companyFields.querySelector('input[name="Person[businessName]"]').required = false;
            companyFields.querySelector('input[name="Person[companyTaxId]"]').required = false;

        } else {
            individualFields.style.display = 'none';
            companyFields.style.display = 'block';

            individualFields.querySelector('input[name="Person[firstName]"]').required = false;
            individualFields.querySelector('input[name="Person[lastName]"]').required = false;
            individualFields.querySelector('input[name="Person[individualTaxId]"]').required = false;

            companyFields.querySelector('input[name="Person[businessName]"]').required = true;
            companyFields.querySelector('input[name="Person[companyTaxId]"]').required = true;
        }
    }

    individualRadio.addEventListener('change', togglePersonType);
    companyRadio.addEventListener('change', togglePersonType);

    togglePersonType();

</script>

<script>
    const countySelect = document.getElementById('countySelect');
    const localitySelect = document.getElementById('localitySelect');

    const hiddenCountyId = document.getElementById('hiddenCountyId');
    const hiddenCountyCode = document.getElementById('hiddenCountyCode');
    const hiddenLocalityName = document.getElementById('hiddenLocalityName');
    const hiddenLocalitySiruta = document.getElementById('hiddenLocalitySiruta');

    function resetLocalities() {
        localitySelect.innerHTML = '<option value="">Select City</option>';
        hiddenLocalityName.value = '';
        hiddenLocalitySiruta.value = '';
    }

    function populateLocalities(localities) {
        localities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.name;
            option.textContent = city.name;
            option.dataset.siruta = city.siruta;
            localitySelect.appendChild(option);
        });
    }

    countySelect.addEventListener('change', async () => {
        const selected = countySelect.selectedOptions[0];
        const id = selected?.value || '';
        const code = selected?.dataset.code || '';

        hiddenCountyId.value = id;
        hiddenCountyCode.value = code;

        resetLocalities();

        if (!id) return;

        try {
            const response = await fetch(`/api/nomenclature/locality/${code}`);
            const data = await response.json();
            if (!data.error && Array.isArray(data.data)) {
                populateLocalities(data.data);
            }
        } catch (err) {
            console.error('Error fetching localities:', err);
        }
    });

    localitySelect.addEventListener('change', () => {
        const selected = localitySelect.selectedOptions[0];
        hiddenLocalityName.value = selected?.value || '';
        hiddenLocalitySiruta.value = selected?.dataset.siruta || '';
    });
</script>


<style>

    body{
        background:#eef2f6;
        font-family: system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
        margin:0;
    }

    /* WRAPPER */

    .rca-wrapper{
        display:flex;
        justify-content:center;
        padding:50px 20px;
    }

    /* CARD */

    .rca-form{
        background:white;
        max-width:900px;
        width:100%;
        padding:40px;
        border-radius:14px;
        box-shadow:0 12px 30px rgba(0,0,0,0.08);
    }

    /* HEADINGS */

    .rca-form h1{
        text-align:center;
        margin-bottom:35px;
        font-size:30px;
    }

    .rca-form h2{
        margin-top:40px;
        font-size:20px;
        color:#111;
        padding-bottom:10px;
        border-bottom:2px solid #f1f1f1;
    }

    /* FIELD ROW */

    .field{
        display:grid;
        grid-template-columns:240px 1fr;
        align-items:center;
        gap:12px;
        margin-bottom:14px;
    }

    /* LABEL */

    .field label{
        font-weight:500;
        color:#333;
    }

    /* INPUTS */

    .rca-form input,
    .rca-form select{
        width:100%;
        padding:11px 14px;
        border:1px solid #d8dbe0;
        border-radius:8px;
        font-size:14px;
        transition:0.2s ease;
        background:#fafbfc;
    }

    .rca-form input:hover,
    .rca-form select:hover{
        border-color:#c4c9d1;
    }

    .rca-form input:focus,
    .rca-form select:focus{
        outline:none;
        background:white;
        border-color:#4f46e5;
        box-shadow:0 0 0 3px rgba(79,70,229,0.12);
    }

    /* RADIO GROUP */

    .radio-group{
        display:flex;
        gap:20px;
        align-items:center;
        margin-bottom:20px;
    }

    .radio-group input{
        transform:scale(1.1);
        cursor:pointer;
    }

    /* ERROR */

    .error{
        background:#ffe6e6;
        color:#cc0000;
        padding:12px;
        border-radius:8px;
        margin-bottom:20px;
    }

    /* SUB SECTIONS */

    .section{
        background:#fafbfc;
        padding:20px;
        border-radius:10px;
        margin-top:20px;
        border:1px solid #eee;
        margin-bottom: 10px;
    }

    /* RESPONSIVE */

    @media(max-width:700px){

        .field{
            grid-template-columns:1fr;
        }

        .rca-form{
            padding:25px;
        }

    }

    input[type="radio"]:focus{
        outline:none;
    }

    input[type="radio"]:focus-visible{
        box-shadow:0 0 0 2px rgba(79,70,229,0.4);
    }

    .submit-btn{
        background:#4f46e5;
        color:white;
        border:none;
        padding:14px 24px;
        border-radius:10px;
        font-size:15px;
        font-weight:600;
        cursor:pointer;
        transition:0.2s ease;
    }

    .submit-btn:hover{
        background:#4338ca;
        transform:translateY(-1px);
        box-shadow:0 6px 14px rgba(0,0,0,0.12);
    }

    .submit-btn:active{
        transform:translateY(0);
        box-shadow:none;
    }


</style>



