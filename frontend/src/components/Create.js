// import first, then require. NPM seems to be anal about it
import React from "react";
import styled from "styled-components/macro";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPlusCircle } from '@fortawesome/free-solid-svg-icons'
import MultipleInputs from "./MultipleInputs";
const Selectables = require('../constants/selectables');
const TableEntries = require('../constants/TableEntries');
const API = require("../API/backendAPI");


const Input = styled.input`
margin: 0 0.5rem   
`
const InputWrapper = styled.div``

function createNewMultiple(map, type) {
    console.log('clicked me!')
    let newMap = map;
    newMap.push(
        <FontAwesomeIcon key={ 'add-'} icon={faPlusCircle} onClick={() => createNewMultiple(map)}/>        
    )
    
}

function createForm(type) {
    // map the list to its respective containers
    let key = 0;
    console.log(type);
    console.log(TableEntries[type])
    let elementMap = TableEntries[type].map(({name, type, max, multiple, subcat}) => {
        if (type==='select') {
            // create dropdown from DB 
            API.getRequest(type, 'get_data')
                .then(data => {console.log(data);})
                .catch(err => console.error(err));
        } else if (multiple !== undefined && multiple === true) {
            // can have multiple entries, make that here.
            let multElementMap = subcat.map(({name, type}) => {
                if (type === 'select') {
                    // create dropdown here
                    // How do I get the data out of here????
                }
                return renderInput({name: name, type: type}, 'createForm'+ key++)
            });
            return (
                <MultipleInputs map={multElementMap} />
            )
        }
        return renderInput({max, name, type}, 'createForm'+ key++);
    }); 
    elementMap.push(<Input key='createSubmit' type='submit' value='Submit'/>)
    return (
        <form key='inputForm' onSubmit={onSubmitForm}>
            {elementMap}
        </form>
    );
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
function onSubmitForm(event){
    // Handle a submit here
    event.preventDefault();
    // paint the elements in red if they are missing information or if there is an error
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
        this.setState({createbox: createForm(event.target.value)});
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
        let element = (<div>
            <label>Choose a category: </label>
            <select onChange={this.createTableFromSelect}>
                <option value="invalid">Select</option>
                {selects}
            </select>
            <div id="create-box">{this.state.createbox}</div>
        </div>)
        console.log(element)
        return element;
    }
}

export default Create; 