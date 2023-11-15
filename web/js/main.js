const generatedImage = document.getElementById('generated-image');
const loadingSpinner = document.getElementById('loading-spinner');
const dynamicInputFields = document.getElementById('dynamic-input-fields');


const isEmpty = str => !str.trim().length;
const addInput = () => {
    const inputField = document.createElement('div');
    inputField.className = 'form-group';
    inputField.innerHTML = `
        <hr/>
        <div class="input-group row">
            <div class="col-md-1">
                <button class="btn btn-outline-secondary" type="button">X</button>
            </div>
            <div class="col-md-2">
                <select class="form-control">
                    <option value="q" data-bs-example="Default 90, Example: 80">quality</option>
                    <option value="o" data-bs-example="Default auto, possible value auto | input | png | webp | jpg">output</option>
                    <option value="rf" data-bs-example="It will force a re-request of the original image, Default 0 possible value 1">refresh</option>
                    <option value="unsh" data-bs-example="Sharpens an image, usage {radius}x{sigma}[+gain][+threshold], examples: 0x6 | 0.25x0.25+8+0.065 ">unsharp</option>
                    <option value="sh" data-bs-example="Use a Gaussian operator of the given radius and standard deviation (sigma), usage {radius}x{sigma}, example: 3 | 0x5">sharpen</option>
                    <option value="fc" data-bs-example="Default ">face-crop</option>
                    <option value="fcp" data-bs-example="Default ">face-crop-position</option>
                    <option value="fb" data-bs-example="Default ">face-blur</option>
                    <option value="w" data-bs-example="Default ">width</option>
                    <option value="h" data-bs-example="Default ">height</option>
                    <option value="c" data-bs-example="Default ">crop</option>
                    <option value="bg" data-bs-example="Default ">background</option>
                    <option value="st" data-bs-example="Default ">strip</option>
                    <option value="ao" data-bs-example="Default ">auto-orient</option>
                    <option value="rz" data-bs-example="Default ">resize</option>
                    <option value="g" data-bs-example="Default ">gravity</option>
                    <option value="f" data-bs-example="Default ">filter</option>
                    <option value="r" data-bs-example="Default ">rotate</option>
                    <option value="sc" data-bs-example="Default ">scale</option>
                    <option value="e" data-bs-example="Default ">extract</option>
                    <option value="sf" data-bs-example="Default ">sampling-factor</option>
                    <option value="smc" data-bs-example="Default ">smart-crop</option>
                    <option value="ett" data-bs-example="Default ">extent</option>
                    <option value="par" data-bs-example="Default ">preserve-aspect-ratio</option>
                    <option value="pns" data-bs-example="Default ">preserve-natural-size</option>
                    <option value="webpl" data-bs-example="Default ">webp-lossless</option>
                    <option value="gf" data-bs-example="Default ">gif-frame</option>
                    <option value="p1x" data-bs-example="Default ">extract-top-x</option>
                    <option value="p1y" data-bs-example="Default ">extract-top-y</option>
                    <option value="p2x" data-bs-example="Default ">xtract-bottom-x</option>
                    <option value="p2y" data-bs-example="Default ">xtract-bottom-y</option>
                    <option value="pg" data-bs-example="Default ">page_number</option>
                    <option value="tm" data-bs-example="Get a video image to fit dimensions from a time duration point, Possible value 00:00:05 ">time</option>
                    <option value="clsp" data-bs-example="Converting to Colorspace Gray, Possible value Gray">colorspace</option>
                    <option value="mnchr" data-bs-example="Converting to Monochrome, Possible value 1">monochrome</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control option-value" placeholder="Enter value" />
            </div>
            <div class="col-md">
                <div class="p-2 bg-info bg-opacity-10 border-info rounded row">
                    <div class="col-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                          <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                        </svg>
                    </div>
                    <div class="example-info col">Default 90 | Example 80</div>
                </div>
            </div>
        </div>
    `;
    dynamicInputFields.appendChild(inputField);
    let deleteBtn = inputField.querySelector('button')
    deleteBtn.addEventListener('click', function () {
        removeInput(this);
    }, false);
    let selectInput = inputField.querySelector('select')
    let exampleBox = inputField.querySelector('div.example-info')

    selectInput.addEventListener('change', function () {
        exampleBox.innerHTML = this.querySelector('option:checked').getAttribute('data-bs-example')
    }, false);

};

const removeInput = button => {
    dynamicInputFields.removeChild(button.closest('.form-group'));
};


const refreshImage = () => {
    let sourceImage = document.getElementById('source-image')
    let sourceImageValidation = document.getElementById('source-image-validation')
    let errorOptions = document.getElementById('error-options');

    if (isEmpty(sourceImage.value)) {
        sourceImage.setAttribute('class', 'form-control is-invalid');
        sourceImageValidation.style.display = 'block';
        return;
    }

    //option-value
    sourceImageValidation.style.display = 'none';
    errorOptions.style.display = 'none';
    sourceImage.setAttribute('class', 'form-control is-valid');

    const inputs = dynamicInputFields.querySelectorAll('.input-group');

    // Combine input values to form the image parameters
    let imageParams = '';
    inputs.forEach((input, index) => {
        let input_option = input.querySelector('input')
        if (isEmpty(input_option.value)) {
            input_option.setAttribute('class', 'form-control is-invalid');
            return;
        }
        input_option.setAttribute('class', 'form-control is-valid');
        let input_key = input.querySelector('option:checked').value
        imageParams += `${input_key}_${encodeURIComponent(input_option.value)},`;
    });

    // Get the base image URL
    const baseUrl = window.location.href + 'upload/';

    // Concatenate parameters with commas and append to the base URL
    let params = imageParams.slice(0, -1);
    if (!params) {
        errorOptions.style.display = 'block';
        return;
    }

    const imageUrl = baseUrl + params + '/' + sourceImage.value; // Remove the trailing comma
    document.getElementById('generated-url').innerHTML = imageUrl;

    showLoading();
    // Set the image source

    generatedImage.onload = hideLoading; // Hide loading spinner on image load
    generatedImage.src = imageUrl;

};

const showLoading = () => {
    // Display loading spinner
    loadingSpinner.style.display = 'block';
    generatedImage.style.display = 'none';
};

const hideLoading = () => {
    // Hide loading spinner
    loadingSpinner.style.display = 'none';
    generatedImage.style.display = 'block';
};


// self executing function here
(function () {
    let refreshBtn = document.getElementById('refresh-btn');
    refreshBtn.addEventListener("click", function () {
        refreshImage();
    }, false);

    let addBtn = document.getElementById('add-btn');
    addBtn.addEventListener("click", function () {
        addInput();
    }, false);

    let copyBtn = document.getElementById('copy-btn');
    copyBtn.addEventListener("click", function () {
        let copyText = document.getElementById("generated-url");
        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.innerHTML);
    }, false);

})();