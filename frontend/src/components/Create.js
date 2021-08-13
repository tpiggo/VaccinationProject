// import first, then require. NPM seems to be anal about it
import React from "react";
import styled from "styled-components/macro";
import backendAPI from "../API/backendAPI";
import MultipleInputs from "./MultipleInputs";
const Selectables = require('../constants/selectables');
const TableEntries = require('../constants/TableEntries');
const API = require("../API/backendAPI");


const Input = styled.input`
margin: 0 0.5rem   
`
const InputWrapper = styled.div``


function getDbData(type, callbackFromData) {
    let callables = []; 
    TableEntries[type].forEach(item => {
        if (item.type === 'select'){
            let route = item.name.replaceAll(' ', '');
            callables.push(API.getRequest(route, 'get_data'));
        } else if (item.multiple !== undefined && item.multiple === true) {
            item.subcat.forEach(scItem => {
                if (scItem.type === 'select') {
                    let route = item.name.replaceAll(' ', '');
                    callables.push(API.getRequest(route, 'get_data'));
                }
            });
        }
    });
    return Promise.all(callables).then(array => callbackFromData(type, array));
}


function createForm(type, fetchedData) {
    return new Promise ((resolve) => {
        // map the list to its respective containers
        // find all information needed from the backend
        let key = 0;
        let elementMap = TableEntries[type].map(({name, type, max, multiple, subcat}) => {
            if (type==='select') {
                // create dropdown from DB 
                // find in fetchedData
                let sKey = 0;
                let selectMap = [
                    <option key={'select-'+ sKey++} value={'invalid'}>Select</option>
                ]
                fetchedData.forEach(item => {
                    if (item.response.name === name.replaceAll(" ", '')) {
                        let typeName = item.response.name.substring(0,1).toLowerCase() + item.response.name.substring(1);
                        item.response.data.forEach(data => {
                            selectMap.push(
                                <option key={'select-'+ sKey++} value={data[typeName]}>{data[typeName]}</option>
                            );
                        });
                    }
                });
                return (
                    <InputWrapper key={'list-wrapper'+ key++}>
                        <label key={'label-list'+key++}>{name}</label>
                        <select name={name.replaceAll(" ", '')} key={'inner-select-'+ key++}>
                            {selectMap}
                        </select>
                    </InputWrapper>
                );
            } else if (multiple !== undefined && multiple === true) {
                // can have multiple entries, make that here.
                let multElementMap = subcat.map(({name, type}) => {
                    if (type === 'select') {
                        // create dropdown here
                        // find in fetched data
                    }
                    return renderInput({name: name, type: type}, 'createForm'+ key++)
                });
                return (
                    <MultipleInputs id={'multiple-box'} map={multElementMap} />
                )
            }
            return renderInput({max, name, type}, 'createForm'+ key++);
        }); 
        elementMap.push(<Input key='createSubmit' type='submit' value='Submit'/>)
        resolve(
            <form id='inputForm' key='inputForm' onSubmit={(e) => onSubmitForm(e, type)}>
                {elementMap}
            </form>
        );
    });
}

const renderInput = ({max, name, type}, key) => {
    let innerInp;
    if (max === undefined) {
        innerInp = <Input name={name.replaceAll(" ", '')}key={key} type={type} />; 
    } else {
        innerInp =<Input name={name.replaceAll(" ", '')}key={key} type={type} inputMaxLength={max}/>        
    }
    return (<InputWrapper key={key + '-wrap'}>
        <label key={key + '-label'}>{name}</label> 
        {innerInp}       
    </InputWrapper>)
};

/**
 * 
 * @param {Event} event 
 */
function onSubmitForm(event, type){
    // Handle a submit here
    function removeInputInformation(child) {
        let inputValue;
        let input = child.getElementsByTagName('input');
        if (input.length !== 0){
            inputValue = {
                name: input[0].getAttribute('name'),
                value: input[0].value
            };
        } else {
            let select = child.getElementsByTagName('select');
            if (select.length !== 0) {
                inputValue = {
                    name: select[0].getAttribute('name'),
                    value: select[0].value
                };
            }
        }
        return inputValue;
    }
    event.preventDefault();
    // paint the elements in red if they are missing information or if there is an error
    console.log('Submitted but stopped');
    let inputValues = []
    let child = document.getElementById('inputForm').firstElementChild;
    while (child !== undefined || child !== null) {
        if (child.className.includes('Create__InputWrapper')) {
            inputValues.push(removeInputInformation(child))
    
        } else if (child.className.includes('MultipleInputs__InputWrapper')) {
            console.log(child)
            let children = [...child.children];
            children.forEach(itemChild => {
                console.log(itemChild)
                if (itemChild.tagName !== 'svg' && itemChild.className.includes('Create__InputWrapper')){
                    console.log('extracting data')
                    inputValues.push(removeInputInformation(itemChild))
                }
            });
        }  
        // going through the linked list
        child = child.nextElementSibling;
    }
    console.log(inputValues);
    console.log(type);
    backendAPI.postRequest(type, inputValues)
    .then(res=> console.log(res))
    .catch(err => console.error(err));
    
}

class Create extends React.Component{
    constructor(props) {
        super(props);
        this.state = {
            createbox: undefined
        }
    }

    createTableFromSelect = (event) => {
        // Do nothing if the first value is current chosen
        if (event.target.value === 'invalid') return;
        getDbData(event.target.value, createForm)
            .then(element => this.setState({createbox: element}))
            .catch(err => console.error(err));
    }

    render () {
        let keyInt = 0;
        let selects = Selectables.selectables.map((item) => {
            let value = item.replaceAll(" ", "");
            value = value.replaceAll("-","");
            value = value.substring(0,1).toLowerCase() + value.substring(1);
            return (
                <option key={"createOption" + keyInt++} value={value}>{item}</option>
            )
        });
        return (
            <div key={'main-div-create'}>
                <h1>Create</h1>
                <label>Choose a category: </label>
                <select onChange={this.createTableFromSelect}>
                    <option value="invalid">Select</option>
                    {selects}
                </select>
                <div id="create-box">{this.state.createbox}</div>
            </div>
        );
    }
}

export default Create; 