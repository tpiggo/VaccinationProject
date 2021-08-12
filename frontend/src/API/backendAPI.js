// need better names

module.exports = {
    getRequest: (entity, type) => {
        let url = `/backend/DBGetApi.php?type=${type}&entity=${entity}`;
        // returns the promise with the JSON data within the next then
        return fetch(url,  {
            headers : { 
            'Content-Type': 'application/json'
            }
        })
        .then(response => response.json());
    },
    postRequest: (type, data) => {
        let url = '/backend/DBPostApi.php';
        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({type: type, data: data})
        }).then(response=>response.json())
    }
};