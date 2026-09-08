function showFileName(input){

    const label = document.getElementById("selectedFile");

    if(input.files.length){

        label.innerHTML = "📄 " + input.files[0].name;

    }else{

        label.innerHTML = "No file selected";

    }

}