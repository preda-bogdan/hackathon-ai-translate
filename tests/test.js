const { Configuration, OpenAIApi } = require("openai");

const configuration = new Configuration({
    apiKey: process.env.OPENAI_API_KEY,
});
const openai = new OpenAIApi(configuration);


//Create a jest test
test('test', async () => {
    const translatePrompt = "Translate this into French, preserve formatting: ";

    const tokenList = [
        {
            "original": "Going to the store to get some milk.",
            "translated": "Going to the store to get some milk.",
        },
        {
            "original": "Going to the store to get some water.",
            "translated": "Going to the store to get some water.",
        },
    ];

    const promptArray = tokenList.map((token) => {
            return `${translatePrompt}${token.original}`;
    });

    console.log(promptArray);


    const response = await openai.createCompletion({
        model: "text-davinci-003",
        //prompt: "Translate this into French, preserve formatting : Going to the store to get some milk.",
        prompt: promptArray,
        temperature: 0.3,
        max_tokens: 100,
        top_p: 1.0,
        frequency_penalty: 0.0,
        presence_penalty: 0.0,
    });
    console.log(response.data.choices);
});
