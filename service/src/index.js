//this is hacky package.json here and node_modules and so on not proud of it
//but works for now
const { Configuration, OpenAIApi } = require("openai");


const configuration = new Configuration({
    apiKey: process.env.OPENAI_API_KEY,
});
const sleep = function (ms) {
    return new Promise((resolve) => {
        setTimeout(resolve, ms);
    });
};
const openai = new OpenAIApi(configuration);
exports.handler = async function(event) {
    console.log(event);

    let tokens = [];
    let language = "French";
    if ( event.body !== undefined && event.body !== null
     ) {

        let body = JSON.parse(event.body);
        console.log(body);
        if ( body.tokens !== undefined && body.tokens !== null ) {
            tokens = body.tokens;
        }
        if ( body.language !== undefined && body.language !== null ) {
            language = body.language;
        }
    }
    //TODO check this and finish it
    console.log(tokens);
    if ( tokens.length >= 20 ) {
        return { statusCode : 400, body : "Too many tokens provided" };
    }
    if ( tokens.length === 0 ) {
        return { statusCode : 400, body : "No tokens provided" };
    }
    const translatePrompt = `Decode this base64 string, then translate it from English to ${language} preserving the HTML structure:`;

    const promptArray = tokens.map((token) => {
        return `${translatePrompt}${token.original}`;
    });

    console.log(promptArray);
    for (let prompt of promptArray) {
        await sleep(2000);
        console.log(prompt);
        const response = await openai.createCompletion({
            model: "text-davinci-003",
            prompt: prompt,
            temperature: 0.3,
            max_tokens: 500,
            top_p: 1.0,
            frequency_penalty: 0.0,
            presence_penalty: 0.0,
        });
        response.data.choices.forEach((choice) => {
            tokens[choice.index].translated =  new Buffer( choice.text.trim() ).toString( 'base64' );
        });
    }


    console.log(tokens);



    return {
        statusCode: 200,
        headers: { "Content-Type": "text/json" },
        body: tokens
    };
};