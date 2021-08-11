module.exports = {
    person: [
        {
            name: "First Name",
            type: 'text',
            max: 225
        },
        {
            name: "Last Name",
            type: 'text',
            max: 225
        },
        {
            name: "Email",
            type: 'email',
            max: 225
        },
        {
            name: "Phone",
            type: 'text',
            max: 225
        },
        {
            name: "Date Of Birth",
            type: 'date'
        },
        {
            name: "Passport",
            type: 'text',
            max: 225
        },
        {
            name: "Medicare Number",
            type: 'text',
            max: 225
        },
        {
            name: 'Address',
            type: 'text',
            max: 225
        },
        {
            name: "City",
            type: 'text',
            max: 225
        },
        {
            name: "Postal Code",
            type: 'text',
            max: 225
        },
        {
            name: "Province",
            type: 'dropdown'
        },
        {
            name: "Country Name",
            type: 'dropdown'
        },
        {
            name: 'infection',
            multiple: true,
            subcat: [
                {
                    name: 'Infection Date',
                    type: 'date'
                },
                {
                    name: "Type Of Infection",
                    type: 'dropdown'
                }
            ]
        }
    ],
    publicHealthWorker: [
        {
            name: "First Name",
            type: 'text',
            max: 225
        },
        {
            name: "Last Name",
            type: 'text',
            max: 225
        },
        {
            name: "SSN",
            type: 'number',
            max: 225
        },
        {
            name: "Email",
            type: 'email',
            max: 225
        },
        {
            name: "Phone",
            type: 'tel'
        },
        {
            name: "Date Of Birth",
            type: 'date'
        },
        {
            name: "Passport",
            type: 'text',
            max: 225
        },
        {
            name: "Medicare Number",
            type: 'text',
            max: 225
        },
        {
            name: 'Address',
            type: 'text',
            max: 225
        },
        {
            name: "City",
            type: 'text',
            max: 225
        },
        {
            name: "Postal Code",
            type: 'text',
            max: 225
        },
        {
            name: "Province",
            type: 'select'
        },
        {
            name: "Country Name",
            type: 'select'
        }
    ],
    publicHealthFacility: [
        {
            name: "Location Name",
            type: 'text',
            max: 225
        },
        {
            name: "Phone",
            type: 'tel'
        },
        {
            name: "Address",
            type: 'text',
            max: 225
        },
        {
            name: "Postal Code",
            type: 'text',
            max: 225
        },
        {
            name: "City",
            type: 'text',
            max: 225
        },
        {
            name: "Province",
            type: 'dropdown'
        },
        {
            name: "Type Of Facility",
            type: 'text',
            max: 225
        },
        {
            name: "Website",
            type: 'text',
            max: 225
        },
    ],
    vaccinationType: [
        {
            name: 'Type',
            type: 'text',
            max: 225
        },
        {
            name: 'Vaccine Status',
            choices: [
                'Approved',
                'Suspended'
            ]
        },
        {
            name: 'dates',
            multiple: true,
            subcat: [
                {
                    name: 'Date of Approval',
                    type: 'date'
                },
                {
                    name: "Date of Suspension",
                    type: 'date'
                }
            ]
        }
    ],
    covid19InfectionVariantType: [
        {
            name: 'typeInfection',
            type: 'text',
            max: 225
        }
    ],
    groupAge: [
        {
            name: 'Age Group',
            type: 'number'
        },
        {
            name: 'Bottom Range',
            type: 'number'
        },
        {
            name: 'Top Range',
            type: 'number'
        }
    ],
    province: [
        {
            name: 'province',
            type: 'text',
            max: 225
        }
    ]
}