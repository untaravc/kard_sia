import { getApps, initializeApp } from 'firebase/app';
import { getDownloadURL, getStorage, ref, uploadBytes } from 'firebase/storage';
import { useFirebaseConfigStore } from './stores/firebaseConfig';

export const uploadFirebaseFile = async ({ file, prefix }) => {
    if (!file) {
        return null;
    }

    const firebaseConfigStore = useFirebaseConfigStore();
    const config = await firebaseConfigStore.fetchConfig();
    if (!config) {
        return null;
    }

    if (!getApps().length) {
        initializeApp(config);
    }

    const storage = getStorage();
    const safeName = `${Date.now()}-${file.name}`.replace(/\s+/g, '-');
    const storageRef = ref(storage, `${prefix}/${safeName}`);
    await uploadBytes(storageRef, file);
    const url = await getDownloadURL(storageRef);
    return url;
};
