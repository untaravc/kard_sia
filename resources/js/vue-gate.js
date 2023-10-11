export default class Gate {
    isSadmin(){
        return window.mewnesia === 'superadmin'
    }

    isPerki(){
        return window.mewnesia === 'perki'
    }
}
