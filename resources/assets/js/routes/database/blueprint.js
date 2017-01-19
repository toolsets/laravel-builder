const AutoUnsignedColumns = [
    "unsignedBigInteger",
    "unsignedInteger",
    "unsignedMediumInteger",
    "unsignedSmallInteger",
    "unsignedTinyInteger",
];

export const IncrementColumns = [
    "bigIncrements",
    "increments",
    "mediumIncrements",
    "smallIncrements"
];


const IntegerTypeColumns = [
    "bigInteger",
    "integer",
    "mediumInteger",
    "smallInteger",
    "tinyInteger"
];

export function makeTableColumn(col) {

    var typeName = col.attributes.type;

    if(col.attributes.unsigned) {

        if(col.attributes.autoIncrement == true) {
            if(IntegerTypeColumns.indexOf(typeName) !== -1) {
                var newTypeName = _.replace(_.upperFirst(typeName), 'Integer', 'Increments');
                newTypeName = _.lowerFirst(newTypeName);

                if(IncrementColumns.indexOf(newTypeName) !== -1) {
                    col.attributes.type = newTypeName;
                }
            }


        } else if(IntegerTypeColumns.indexOf(typeName) !== -1) {
            var newType = 'unsigned' + _.upperFirst(typeName);
            if(AutoUnsignedColumns.indexOf(newType) !== -1) {
                col.attributes.type = newType;
            }
        }



    }

    return col;
}

