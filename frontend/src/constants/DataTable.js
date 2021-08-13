/**
 * @param {{response: ?}} data 
 */
 const createTableFromData = function(data) {
    return new Promise((resolve, reject) => {
        if ( typeof data.response === 'string' || data.response instanceof String) {
            // output error!
            return reject({error: "No data"});
        }
        // type must be an array
        console.log(data.response.data)
        let i = 0;
        let tableHead = Object.keys(data.response.data[0]).map((key) => (
            <th key={'tableHead' + i++ }>{key}</th>
        )); 
    
        let body = data.response.data.map((row) => (
            <tr key={'tableRow' + i++}>
                {Object.keys(row).map((key) => (
                    <td key={'td' + i++}>{row[key]}</td>
                ))}
            </tr>
        ));
    
        resolve (
            <table>
                <thead>
                    <tr>
                        {tableHead}
                    </tr>
                </thead>
                <tbody>{body}</tbody>
            </table>
        )
    });
}
export default createTableFromData;