import React from "react";
import  createTableFromData from  "../constants/DataTable";
const Selectables = require('../constants/selectables');
const API = require("../API/backendAPI");

class Display extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            displayBox: undefined
        }
    }

    queryDataFromSelect = (event) => {
        console.log(event.target.value);
        if (event.target.value === 'invalid') {
            // Do nothing
            this.setState({displayBox: undefined});
            return;
        }
        API.getRequest(event.target.value, 'get_data')
            .then(res => createTableFromData(res))
            .then(element => this.setState({displayBox: element}))
            .catch(err => console.error(err));
    }

    render () {
        let keyInt = 0;
        let selects = Selectables.displayables.map((item) => {
            let value = item.replaceAll(" ", "");
            value = value.replaceAll("-","");
            value = value.substring(0,1).toLowerCase() + value.substring(1);
            return (
                <option key={"queryOption" + keyInt++} value={value}>{item}</option>
            )
        });

        return (
            <div key={'main-div-display'}>
                <h1>Display</h1>
                <label>Choose a category: </label>
                <select onChange={this.queryDataFromSelect}>
                    <option value="invalid">Select</option>
                    {selects}
                </select>
                <div id="display-box">{this.state.displayBox}</div>
            </div>
        );
    }
}

export default Display;