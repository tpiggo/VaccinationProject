import React from "react";
const Selectables = require('../constants/selectables')
function getRowsFromDB(event) {
    // Do nothing if the first value is current chosen
    if (event.target.value === 'invalid') return;
    
    console.log("the type you picked is " + event.target.value)
}

function callBackend(type) {
    let url = `/backend/DBApi.php?get_rows=true&type=${type}`
	fetch(url,  {
		headers : { 
		'Content-Type': 'application/json'
		}
	})
	.then(response => response.json())
	.then(data => console.log(data));
}

const Create = (props) => {
    let keyInt = 0;
    let selects = Selectables.selectables.map((item) => 
        (<option key={"createOption" + keyInt++} value={item.toLowerCase().replaceAll(" ", "-")}>{item}</option>)
    );
    return (
        <div>
            <label>Choose a category: </label>
            <select onChange={getRowsFromDB}>
                <option value="invalid">Select</option>
                {selects}
            </select>
            <div id="create-box">

            </div>
        </div>
    )
}

export default Create; 