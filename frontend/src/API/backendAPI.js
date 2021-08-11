module.exports = {
    getRequest: (entity, type) => {
        let url = `/backend/DBGetApi.php?type=${type}&entity=${entity}`
        // returns the promise with the JSON data within the next then
        return fetch(url,  {
            headers : { 
            'Content-Type': 'application/json'
            }
        })
        .then(response => response.json());
    }
};