import React from 'react';
import backendAPI from '../API/backendAPI';

/**
 * 
 * @param {{response: {data: Array<JSON>}}} data 
 * @param {Function} callback 
 * @returns 
 */
function createSelectFromData(id, data, callback) {
    console.log(data);
    let i = 0;
    let selects = data.response.data.map((item) => {
        return Object.keys(item).map((key) => (
            <option key={key + i++} value={item[key]}>{item[key]}</option>
        ))
    });
    return (
        <select id={id} onClick={callback}>
            <option value='invalid'>Select</option>
            {selects}
        </select>
    )
}

/**
 * Returns the data back to the proper form for the above function.
 * @param {{response: {data: Array<JSON>}}} res
 * @returns {response: {data: Array<JSON>}}
 */
function convertResToJson(res) {
    console.log(res);
    
    let newRes = {response: {data : res.response.data.map(item => {
        return item.ageGroup;
    }) }};
    console.log(newRes);
    return newRes;
}

class NewGroupAge extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            provinceSelect: undefined
        };
    }

    // Set the provinces a the rendering
    componentDidMount() {
        this.setProvinces();
    }

    /**
     * 
     * @param {Event} event 
     */
    submitForm = (event) => {
        event.preventDefault();
        let province = document.getElementById('province-select').value;
        let ageGroup = document.getElementById('age-group-select').value;
        console.log(province, ageGroup);
        backendAPI.postRequest('setGroupAge', [province, ageGroup])
            .then(res => console.log(res))
            .catch(err => console.error(err));
    }

    /**
     * 
     * @param {Event} event 
     */
    getAgeGroup = (event) => {
        if (event.target.value === 'invalid') {
            return;
        }
        backendAPI.getRequest('groupAge', 'get_data')
        .then(res => this.setState({
            ageGroupSelect: createSelectFromData(
                'age-group-select', 
                convertResToJson(res)
            )
        }))
        .catch(err => console.error(err));
    }

    setProvinces = () => {
        backendAPI.getRequest('province', 'get_data')
        .then(res => this.setState({
            provinceSelect: createSelectFromData('province-select', res, this.getAgeGroup)
        }))
        .catch(err => console.error(err));
    }

    render() {
        return (
            <div>
                <h1>
                    Set the new age group!
                </h1>
                <form onSubmit={this.submitForm}>
                    {this.state.provinceSelect}
                    {this.state.ageGroupSelect}
                    <input value="submit" type="submit" />
                </form>
            </div>
        )
    }
}

export default NewGroupAge;